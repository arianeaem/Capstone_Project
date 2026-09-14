<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Weather Safety Machine Learning Microservice Client.
 *
 * Architecture & Dual-Engine Rationale:
 * This service communicates with the standalone Python FastAPI / ONNX inference microservice.
 * Camp FreedivePH uses a dual-engine architecture:
 * 1. Native PHP Heuristic Engine (WeatherForecastService): Evaluates 9 marine variables against Coast Guard safety rules.
 * 2. Predictive ONNX Microservice (WeatherSafetyMLService): Runs 12 multi-horizon regressor and classifier models
 *    predicting wave dynamics, wind speeds, and ocean currents up to 16 days ahead.
 *
 * Fault Tolerance:
 * If the ML microservice is unreachable, times out, or returns a 5xx error, the client catches the exception,
 * logs an info notice, and gracefully returns `null`. The calling controllers automatically fall back to the
 * native PHP heuristic engine ensuring zero downtime for customers booking sessions.
 */
class WeatherSafetyMLService
{
    protected string $baseUrl;
    protected int $timeout;
    protected bool $enabled;

    public const RISK_TIERS = [
        0 => 'Very Safe',
        1 => 'Safe',
        2 => 'Moderate',
        3 => 'High Risk',
        4 => 'Critical Risk',
    ];

    public const RISK_KEYS = [
        'Very Safe' => 'very_safe',
        'Safe' => 'safe',
        'Moderate' => 'moderate',
        'High Risk' => 'high_risk',
        'Critical Risk' => 'critical_risk',
    ];

    public const OPERATIONAL_STATUS_LABELS = [
        'TACTICAL_CLEARANCE' => 'Tactical Clearance (H ≤ 1h)',
        'PROVISIONAL_TREND_OUTLOOK' => 'Provisional Trend Outlook (6h-24h)',
        'EXTENDED_TREND_OUTLOOK' => 'Extended Trend Outlook (H ≥ 48h)',
    ];

    /**
     * Initializes the ML client from configuration.
     *
     * Note: Timeout is deliberately capped at 4s so slow network conditions
     * never block the customer-facing booking checkout page.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.ml_safety.url', 'http://127.0.0.1:8001'), '/');
        $this->timeout = (int) config('services.ml_safety.timeout', 4);
        $this->enabled = (bool) config('services.ml_safety.enabled', true);
    }

    /**
     * Check if ML Safety microservice is enabled and configured.
     *
     * @return bool True if enabled and baseUrl is present.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->baseUrl);
    }

    // TODO: Implement a circuit breaker pattern (e.g., via Redis) to prevent HTTP connection spam when the microservice is temporarily down.

    /**
     * Assess a dive booking session through the 12 ONNX ML inference pipeline.
     *
     * @param string $date YYYY-MM-DD
     * @param string $startTime HH:MM
     * @param string $endTime HH:MM
     * @param array $boundaryWeather Array of hourly atmospheric readings
     * @param array|null $pagasa Optional PAGASA signals
     * @return array|null Standardized ML assessment result or null on failure/disabled
     */
    public function assessBookingSession(
        string $date,
        string $startTime,
        string $endTime,
        array $boundaryWeather,
        ?array $pagasa = null
    ): ?array {
        if (!$this->isEnabled()) {
            return null;
        }

        if (empty($boundaryWeather)) {
            return null;
        }

        try {
            $payload = [
                'planned_date' => $date,
                'dive_start' => $startTime,
                'dive_end' => $endTime,
                'boundary_weather' => $boundaryWeather,
                'pagasa' => $pagasa ?? [
                    'tcws_signal' => 0,
                    'gale_warning' => false,
                    'tsunami_warning' => false,
                ],
                'site_name' => 'Anilao, Mabini, Batangas',
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/assess-booking", $payload);

            if (!$response->successful()) {
                Log::warning('[WeatherSafetyMLService] ML assessment endpoint returned non-200', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'date' => $date,
                ]);
                return null;
            }

            $data = $response->json();
            return $this->standardizeResponse($data);
        } catch (Exception $e) {
            Log::info('[WeatherSafetyMLService] ML Safety microservice unreachable, continuing with native heuristic engine', [
                'error' => $e->getMessage(),
                'date' => $date,
            ]);
            return null;
        }
    }

    /**
     * Transform raw Open-Meteo hourly readings into the schema required by ML inference.
     */
    public function formatBoundaryWeather(array $hourlyReadings): array
    {
        $formatted = [];

        foreach ($hourlyReadings as $h) {
            $timestamp = $h['time'] ?? $h['timestamp'] ?? null;
            if (!$timestamp) {
                continue;
            }

            $windSpeedKmh = (float) ($h['wind_speed_10m'] ?? $h['wind_speed'] ?? 0.0);
            $windGustKmh = (float) ($h['wind_gusts_10m'] ?? $h['wind_gust'] ?? $windSpeedKmh * 1.25);
            $windDir = (float) ($h['wind_direction_10m'] ?? $h['wind_dir'] ?? 0.0);
            $pressureHpa = (float) ($h['surface_pressure'] ?? $h['slp'] ?? 1012.0);
            $rainMm = (float) ($h['rain'] ?? $h['precipitation'] ?? $h['rain_rate_mm_hr'] ?? 0.0);
            $currentSpeed = isset($h['ocean_current_velocity']) ? (float) $h['ocean_current_velocity'] : (isset($h['current_speed']) ? (float) $h['current_speed'] : null);
            $currentDir = isset($h['ocean_current_direction']) ? (float) $h['ocean_current_direction'] : (isset($h['current_dir']) ? (float) $h['current_dir'] : null);

            $entry = [
                'timestamp' => $timestamp,
                'wind_speed' => round($windSpeedKmh, 2),
                'wind_gust' => round($windGustKmh, 2),
                'wind_dir' => round($windDir, 1),
                'slp' => round($pressureHpa, 2),
                'rain_rate_mm_hr' => round($rainMm, 2),
            ];

            if ($currentSpeed !== null) {
                $entry['current_speed'] = round($currentSpeed, 3);
            }
            if ($currentDir !== null) {
                $entry['current_dir'] = round($currentDir, 1);
            }

            $formatted[] = $entry;
        }

        return $formatted;
    }

    /**
     * Standardize FastAPI response into uniform 5-tier classification structure.
     */
    protected function standardizeResponse(array $raw): array
    {
        $worstHour = $raw['worst_hour'] ?? [];
        $rawRec = $raw['overall_recommendation'] ?? $worstHour['final_tier_name'] ?? $raw['displayed_risk_name'] ?? 'Safe';

        // Standardize recommendation to the 5 official safety tiers: Very Safe, Safe, Moderate, High Risk, Critical Risk
        $recommendation = match (trim($rawRec)) {
            'Very Safe', 'GO' => 'Very Safe',
            'Safe', 'PROVISIONAL_GO' => 'Safe',
            'Moderate', 'CAUTION_ADVANCED_ONLY' => 'Moderate',
            'High Risk', 'HIGH_RISK_NO_GO' => 'High Risk',
            'Critical Risk', 'NO_GO' => 'Critical Risk',
            default => 'Safe',
        };

        $rawTierName = $worstHour['final_tier_name'] ?? $raw['displayed_risk_name'] ?? $recommendation;
        $classification = match (trim($rawTierName)) {
            'Very Safe', 'GO' => 'Very Safe',
            'Safe', 'PROVISIONAL_GO' => 'Safe',
            'Moderate', 'CAUTION_ADVANCED_ONLY' => 'Moderate',
            'High Risk', 'HIGH_RISK_NO_GO' => 'High Risk',
            'Critical Risk', 'NO_GO' => 'Critical Risk',
            default => $recommendation,
        };

        $riskKey = self::RISK_KEYS[$classification] ?? 'safe';
        $opStatus = $raw['overall_operational_status'] ?? 'PROVISIONAL_TREND_OUTLOOK';
        $opStatusLabel = self::OPERATIONAL_STATUS_LABELS[$opStatus] ?? $opStatus;

        return [
            'success' => true,
            'source' => 'ml_onnx_microservice',
            'planned_date' => $raw['planned_date'] ?? null,
            'dive_start' => $raw['dive_start'] ?? null,
            'dive_end' => $raw['dive_end'] ?? null,
            'overall_recommendation' => $recommendation,
            'ml_recommendation' => $recommendation,
            'ml_classification' => $classification,
            'ml_risk_key' => $riskKey,
            'operational_status' => $opStatus,
            'operational_status_label' => $opStatusLabel,
            'is_authoritative_go' => (bool) ($raw['is_authoritative_go'] ?? false),
            'safety_threshold_triggered' => (bool) ($raw['overall_safety_threshold_triggered'] ?? $raw['safety_threshold_triggered'] ?? $raw['overall_hard_gate_triggered'] ?? false),
            'hard_gate_triggered' => (bool) ($raw['overall_safety_threshold_triggered'] ?? $raw['safety_threshold_triggered'] ?? $raw['overall_hard_gate_triggered'] ?? false),
            'worst_hour' => [
                'timestamp' => $worstHour['timestamp'] ?? null,
                'hour' => $worstHour['hour'] ?? null,
                'horizon_hours' => $worstHour['horizon_hours'] ?? null,
                'classification' => $worstHour['final_tier_name'] ?? $classification,
                'primary_hazard' => $worstHour['primary_hazard'] ?? 'Normal Marine Conditions',
                'advisory_message' => $worstHour['advisory_message'] ?? '',
                'safety_threshold_triggered' => (bool) ($worstHour['safety_threshold_triggered'] ?? $worstHour['hard_gate_triggered'] ?? false),
                'hard_gate_triggered' => (bool) ($worstHour['safety_threshold_triggered'] ?? $worstHour['hard_gate_triggered'] ?? false),
                'override_reasons' => $worstHour['override_reasons'] ?? [],
            ],
            'hourly_assessments' => $raw['hourly_assessments'] ?? [],
            'generated_at' => $raw['generated_at'] ?? now()->toIso8601String(),
        ];
    }
}
