<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\User;
use App\Services\WeatherForecastService;
use App\Services\WeatherSafetyMLService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MLSafetyServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected WeatherSafetyMLService $mlService;
    protected WeatherForecastService $forecastService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'must_change_password' => false,
        ]);

        $this->mlService = app(WeatherSafetyMLService::class);
        $this->forecastService = app(WeatherForecastService::class);
    }

    public function test_ml_service_standardizes_all_5_risk_classifications(): void
    {
        $tiers = [
            'Very Safe' => 'very_safe',
            'Safe' => 'safe',
            'Moderate' => 'moderate',
            'High Risk' => 'high_risk',
            'Critical Risk' => 'critical_risk',
        ];

        $currentTier = 'Very Safe';
        Http::fake(function (\Illuminate\Http\Client\Request $request) use (&$currentTier) {
            return Http::response([
                'planned_date' => '2026-09-15',
                'dive_start' => '08:00',
                'dive_end' => '12:00',
                'overall_recommendation' => $currentTier,
                'displayed_risk_name' => $currentTier,
                'overall_operational_status' => 'PROVISIONAL_TREND_OUTLOOK',
                'is_authoritative_go' => ($currentTier === 'Very Safe' || $currentTier === 'Safe'),
                'overall_safety_threshold_triggered' => ($currentTier === 'Critical Risk'),
                'overall_hard_gate_triggered' => ($currentTier === 'Critical Risk'),
                'worst_hour' => [
                    'hour' => 10,
                    'final_tier_name' => $currentTier,
                    'primary_hazard' => 'Test Hazard',
                    'advisory_message' => 'Test Advisory',
                    'safety_threshold_triggered' => ($currentTier === 'Critical Risk'),
                    'hard_gate_triggered' => ($currentTier === 'Critical Risk'),
                ],
            ], 200);
        });

        foreach ($tiers as $tierName => $expectedKey) {
            $currentTier = $tierName;

            $boundaryWeather = [
                [
                    'timestamp' => '2026-09-15T08:00:00+08:00',
                    'wind_speed' => 12.0,
                    'wind_gust' => 15.0,
                    'wind_dir' => 245.0,
                    'slp' => 1012.0,
                    'rain_rate_mm_hr' => 0.0,
                ],
            ];

            $result = $this->mlService->assessBookingSession('2026-09-15', '08:00', '12:00', $boundaryWeather);

            $this->assertNotNull($result);
            $this->assertEquals($tierName, $result['overall_recommendation']);
            $this->assertEquals($tierName, $result['ml_recommendation']);
            $this->assertEquals($tierName, $result['ml_classification']);
            $this->assertEquals($expectedKey, $result['ml_risk_key']);
            $this->assertContains($result['overall_recommendation'], [
                'Very Safe',
                'Safe',
                'Moderate',
                'High Risk',
                'Critical Risk',
            ]);
        }
    }

    public function test_ml_service_gracefully_falls_back_when_microservice_offline(): void
    {
        Http::fake([
            '*/assess-booking' => Http::response('Service Unavailable', 503),
        ]);

        $boundaryWeather = [
            [
                'timestamp' => '2026-09-15T08:00:00+08:00',
                'wind_speed' => 12.0,
                'wind_gust' => 15.0,
                'wind_dir' => 245.0,
                'slp' => 1012.0,
                'rain_rate_mm_hr' => 0.0,
            ],
        ];

        $result = $this->mlService->assessBookingSession('2026-09-15', '08:00', '12:00', $boundaryWeather);
        $this->assertNull($result);
    }

    public function test_assess_batch_integrates_dual_engine_with_ml_recommendation(): void
    {
        Http::fake([
            '*/assess-booking' => Http::response([
                'planned_date' => '2026-09-15',
                'dive_start' => '08:00',
                'dive_end' => '18:00',
                'overall_recommendation' => 'Safe',
                'displayed_risk_name' => 'Safe',
                'overall_operational_status' => 'PROVISIONAL_TREND_OUTLOOK',
                'is_authoritative_go' => false,
                'overall_safety_threshold_triggered' => false,
                'overall_hard_gate_triggered' => false,
                'worst_hour' => [
                    'hour' => 11,
                    'final_tier_name' => 'Safe',
                    'primary_hazard' => 'Normal Marine Conditions',
                    'advisory_message' => 'Safe conditions',
                ],
            ], 200),
            'https://marine-api.open-meteo.com/*' => Http::response([], 200),
            'https://api.open-meteo.com/*' => Http::response([], 200),
        ]);

        $batch = Batch::create([
            'name' => 'Anilao ML Integrated Batch',
            'batch_code' => 'BATCH-ML-001',
            'start_date' => Carbon::now('Asia/Manila')->addDays(3)->format('Y-m-d'),
            'end_date' => Carbon::now('Asia/Manila')->addDays(4)->format('Y-m-d'),
            'status' => 'confirmed',
            'lifecycle_status' => 'confirmed',
        ]);

        $result = $this->forecastService->assessBatch($batch, null, $this->admin);

        $this->assertNotNull($result['overall_classification']);
        $this->assertArrayHasKey('ml_assessment', $result);
        if ($result['ml_assessment'] !== null) {
            $this->assertContains($result['ml_assessment']['overall_recommendation'], [
                'Very Safe',
                'Safe',
                'Moderate',
                'High Risk',
                'Critical Risk',
            ]);
        }
    }

    public function test_api_weather_preview_returns_ml_recommendation(): void
    {
        $targetDate = Carbon::now('Asia/Manila')->addDays(4)->format('Y-m-d');

        Http::fake([
            '*/assess-booking' => Http::response([
                'planned_date' => $targetDate,
                'dive_start' => '08:00',
                'dive_end' => '18:00',
                'overall_recommendation' => 'Very Safe',
                'displayed_risk_name' => 'Very Safe',
                'overall_operational_status' => 'PROVISIONAL_TREND_OUTLOOK',
                'is_authoritative_go' => false,
                'overall_safety_threshold_triggered' => false,
                'overall_hard_gate_triggered' => false,
                'worst_hour' => [
                    'hour' => 10,
                    'final_tier_name' => 'Very Safe',
                    'primary_hazard' => 'Calm Marine Conditions',
                    'advisory_message' => 'Optimal for freediving',
                ],
            ], 200),
            'https://marine-api.open-meteo.com/*' => Http::response([], 200),
            'https://api.open-meteo.com/*' => Http::response([], 200),
        ]);

        $response = $this->getJson("/api/weather/preview?start_date={$targetDate}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'available',
            'start_date',
            'end_date',
            'overall_classification',
            'ml_assessment',
            'day1',
            'day2',
        ]);
    }
}
