<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\BatchRiskAssessment;
use App\Models\BatchStatusLog;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\HourlyAssessment;
use App\Models\ManualOverride;
use App\Models\NotificationLog;
use App\Models\RefundRequest;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherForecastService
{
    // Anilao / Mabini, Batangas Site Coordinates
    public const LATITUDE = 13.7481;
    public const LONGITUDE = 120.9408;
    public const TIMEZONE = 'Asia/Manila';
    public const MAX_FORECAST_DAYS = 16;

    // Weight table incorporating direct wind speed and compound synergies
    public const WEIGHTS = [
        'wave_height' => 0.150,
        'wind_speed' => 0.140,
        'ocean_current' => 0.130,
        'swell_height' => 0.120,
        'wave_period' => 0.110,
        'wind_wave_height' => 0.100,
        'rain' => 0.080,
        'sea_level_pressure' => 0.070,
        'tide_height' => 0.050,
        'wind_direction' => 0.050,
    ];

    public const MEANING_MAP = [
        'Very Safe' => 'Conditions are optimal for freediving. Environmental hazards are minimal.',
        'Safe' => 'Conditions are generally safe, but normal safety protocols should still be followed.',
        'Moderate' => 'Some conditions may affect safety or comfort. Increased monitoring is needed.',
        'High Risk' => 'Conditions present significant hazards that could compromise diver safety.',
        'Critical Risk' => 'Conditions are unsafe for freediving due to severe weather or sea state.',
        'Not Available' => 'Forecast model not yet available for dates beyond 16 days.',
    ];

    public const RISK_RANK = [
        'Not Available' => 0,
        'Very Safe' => 1,
        'Safe' => 2,
        'Moderate' => 3,
        'High Risk' => 4,
        'Critical Risk' => 5,
    ];

    /**
     * Run full risk assessment for a 2D1N Batch across all 4 fixed windows:
     * - Day 1 AM (09:30–12:00) & PM (15:30–17:30)
     * - Day 2 AM (09:30–12:00) & PM (15:30–17:30)
     */
    public function assessBatch(Batch $batch, ?array $overrides = null, ?User $assessedBy = null): array
    {
        return DB::transaction(function () use ($batch, $overrides, $assessedBy) {
            $startDate = $batch->start_date->copy()->startOfDay();
            $endDate = $batch->end_date ? $batch->end_date->copy()->startOfDay() : $startDate->copy()->addDay();

            $assessedAt = now();

            // 1. Assess Day 1
            $day1Result = $this->assessDay($batch, 1, $startDate, $overrides, $assessedBy, $assessedAt);

            // 2. Assess Day 2
            $day2Result = $this->assessDay($batch, 2, $endDate, $overrides, $assessedBy, $assessedAt);

            // 3. Determine Overall Batch Classification (worse of Day 1 and Day 2)
            $rank1 = self::RISK_RANK[$day1Result['classification']] ?? 1;
            $rank2 = self::RISK_RANK[$day2Result['classification']] ?? 1;
            $worseRank = max($rank1, $rank2);
            $overallClassification = array_search($worseRank, self::RISK_RANK) ?: 'Safe';

            // Convert to slug for batches.risk_classification
            $riskSlug = match ($overallClassification) {
                'Very Safe' => 'very_safe',
                'Safe' => 'safe',
                'Moderate' => 'moderate',
                'High Risk' => 'high_risk',
                'Critical Risk' => 'critical_risk',
                'Not Available' => 'safe',
                default => 'safe',
            };

            $batch->update([
                'risk_classification' => $riskSlug,
            ]);

            AuditLogger::log(
                'BATCH_ASSESSED',
                "Weather risk assessed for batch {$batch->batch_code}: Day 1={$day1Result['classification']}, Day 2={$day2Result['classification']} (Overall: {$overallClassification}).",
                $assessedBy,
                $assessedBy ? $assessedBy->name : 'System'
            );

            return [
                'batch' => $batch,
                'overall_classification' => $overallClassification,
                'day1' => $day1Result,
                'day2' => $day2Result,
            ];
        });
    }

    /**
     * Assess an individual day (AM window + PM window) with "Worst Window Wins".
     */
    public function assessDay(Batch $batch, int $dayNumber, Carbon $date, ?array $overrides, ?User $assessedBy, ?Carbon $assessedAt = null): array
    {
        $assessedAt = $assessedAt ?? now();
        $daysOut = Carbon::now(self::TIMEZONE)->diffInDays($date->copy()->startOfDay(), false);
        $leadTimeHours = max(0, Carbon::now(self::TIMEZONE)->diffInHours($date->copy()->setTime(9, 30), false));
        $overrideTriggered = $this->checkOverrideConditions($overrides);

        // Check if date is outside the 16-day forecast model horizon (or in past >1 day)
        if (!$overrideTriggered && ($daysOut > self::MAX_FORECAST_DAYS || $daysOut < -1)) {
            $dayClassification = 'Not Available';
            $recommendedAction = $daysOut > self::MAX_FORECAST_DAYS
                ? "Forecast model is not yet available beyond 16 days out. Assessment will unlock on " . $date->copy()->subDays(16)->format('M d, Y') . " (16 days before dive date)."
                : "Past date - live forecast no longer active.";

            $riskAssessment = BatchRiskAssessment::create([
                'batch_id' => $batch->id,
                'day_number' => $dayNumber,
                'dive_date' => $date->format('Y-m-d'),
                'lead_time_hours' => $leadTimeHours,
                'overall_classification' => $dayClassification,
                'weighted_score_pct' => null,
                'recommended_action' => $recommendedAction,
                'worst_window' => null,
                'worst_hour' => null,
                'override_triggered' => false,
                'override_details' => $overrides,
                'assessed_by' => $assessedBy?->id,
                'assessed_at' => $assessedAt,
            ]);

            return [
                'assessment_id' => $riskAssessment->id,
                'day_number' => $dayNumber,
                'date' => $date->format('Y-m-d'),
                'lead_time_hours' => $leadTimeHours,
                'classification' => $dayClassification,
                'weighted_score_pct' => null,
                'recommended_action' => $recommendedAction,
                'worst_window' => 'N/A',
                'worst_hour' => 'N/A',
                'am' => ['classification' => 'Not Available', 'hourly' => []],
                'pm' => ['classification' => 'Not Available', 'hourly' => []],
            ];
        }

        // Evaluate AM Window (09:30 - 12:00)
        $amData = $this->assessWindow($date->format('Y-m-d'), '09:30', '12:00', 'am', $overrides);

        // Evaluate PM Window (15:30 - 17:30)
        $pmData = $this->assessWindow($date->format('Y-m-d'), '15:30', '17:30', 'pm', $overrides);

        // Worst Window Wins
        $amRank = self::RISK_RANK[$amData['classification']] ?? 1;
        $pmRank = self::RISK_RANK[$pmData['classification']] ?? 1;

        if ($pmRank > $amRank) {
            $worstWindow = '15:30-17:30';
            $dayClassification = $pmData['classification'];
            $weightedScorePct = $pmData['weighted_score_pct'];
            $worstHour = $pmData['worst_hour'];
        } else {
            $worstWindow = '09:30-12:00';
            $dayClassification = $amData['classification'];
            $weightedScorePct = $amData['weighted_score_pct'];
            $worstHour = $amData['worst_hour'];
        }

        // Incorporate 24-Hour Day Peak if worse than window assessments
        $cachedDay = $this->getCachedDayForecast($date->format('Y-m-d'));
        if ($cachedDay && !$overrideTriggered) {
            $cachedRank = self::RISK_RANK[$cachedDay['overall_classification']] ?? 1;
            $currentRank = self::RISK_RANK[$dayClassification] ?? 1;
            if ($cachedRank > $currentRank) {
                $dayClassification = $cachedDay['overall_classification'];
                $weightedScorePct = $cachedDay['overall_score_pct'];
                foreach ($cachedDay['hourly'] as $h) {
                    if (($h['weighted_score_pct'] ?? 0) >= $weightedScorePct) {
                        $worstHour = $h['iso_time'];
                        $worstWindow = sprintf('%02d:00', $h['hour']);
                        break;
                    }
                }
            }
        }

        $recommendedAction = self::MEANING_MAP[$dayClassification] ?? 'Proceed with caution.';

        // Persist BatchRiskAssessment
        $riskAssessment = BatchRiskAssessment::create([
            'batch_id' => $batch->id,
            'day_number' => $dayNumber,
            'dive_date' => $date->format('Y-m-d'),
            'lead_time_hours' => $leadTimeHours,
            'overall_classification' => $dayClassification,
            'weighted_score_pct' => $overrideTriggered ? null : $weightedScorePct,
            'recommended_action' => $recommendedAction,
            'worst_window' => $worstWindow,
            'worst_hour' => $worstHour,
            'override_triggered' => $overrideTriggered,
            'override_details' => $overrides,
            'assessed_by' => $assessedBy?->id,
            'assessed_at' => $assessedAt,
        ]);

        // Persist HourlyAssessments for AM
        foreach ($amData['hourly'] as $h) {
            HourlyAssessment::create(array_merge($h, [
                'risk_assessment_id' => $riskAssessment->id,
                'window_type' => 'am',
                'open_water_window' => '09:30-12:00',
            ]));
        }

        // Persist HourlyAssessments for PM
        foreach ($pmData['hourly'] as $h) {
            HourlyAssessment::create(array_merge($h, [
                'risk_assessment_id' => $riskAssessment->id,
                'window_type' => 'pm',
                'open_water_window' => '15:30-17:30',
            ]));
        }

        return [
            'assessment_id' => $riskAssessment->id,
            'day_number' => $dayNumber,
            'date' => $date->format('Y-m-d'),
            'lead_time_hours' => $leadTimeHours,
            'classification' => $dayClassification,
            'weighted_score_pct' => $weightedScorePct,
            'recommended_action' => $recommendedAction,
            'worst_window' => $worstWindow,
            'worst_hour' => $worstHour ? Carbon::parse($worstHour)->format('g:i A') : 'N/A',
            'am' => $amData,
            'pm' => $pmData,
        ];
    }

    /**
     * Preview assessment for client date selection on the booking form.
     */
    public function previewDateAssessment(Carbon $startDate): array
    {
        $endDate = $startDate->copy()->addDay();
        $today = Carbon::today(self::TIMEZONE);
        $daysOut = $today->diffInDays($startDate->copy()->startOfDay(), false);

        if ($daysOut > self::MAX_FORECAST_DAYS || $daysOut < 0) {
            return [
                'available' => false,
                'message' => $daysOut > self::MAX_FORECAST_DAYS
                    ? "Assessment Not Available. Open-Meteo forecast models are available up to 16 days in advance (currently available through " . $today->copy()->addDays(16)->format('M d, Y') . ")."
                    : "Selected date is in the past.",
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ];
        }

        // Check if day 1 and day 2 are in the unified cache
        $d1Key = $startDate->format('Y-m-d');
        $d2Key = $endDate->format('Y-m-d');

        $d1Cache = $this->getCachedDayForecast($d1Key);
        $d2Cache = $this->getCachedDayForecast($d2Key);

        if (!$d1Cache || !$d2Cache) {
            try {
                $this->updateAllForecasts(16);
                $d1Cache = $this->getCachedDayForecast($d1Key);
                $d2Cache = $this->getCachedDayForecast($d2Key);
            } catch (\Throwable $e) {
                // Ignore failure and fallback to live window calculation
            }
        }

        if ($d1Cache && $d2Cache) {
            // Day 1 & Day 2 Full 24-Hour Day Peak Classifications
            $day1Class = $d1Cache['overall_classification'] ?? 'Safe';
            $day2Class = $d2Cache['overall_classification'] ?? 'Safe';

            // Trip Overall (worse of Day 1 and Day 2 24-hour peaks)
            $worseRank = max(self::RISK_RANK[$day1Class] ?? 1, self::RISK_RANK[$day2Class] ?? 1);
            $overallClass = array_search($worseRank, self::RISK_RANK) ?: 'Safe';

            // Worst hour across the whole day for Day 1
            $d1WorstHour = null;
            $d1MaxScore = -1;
            foreach ($d1Cache['hourly'] ?? [] as $h) {
                if (($h['weighted_score_pct'] ?? 0) > $d1MaxScore) {
                    $d1MaxScore = $h['weighted_score_pct'];
                    $d1WorstHour = $h['iso_time'] ?? null;
                }
            }

            // Worst hour across the whole day for Day 2
            $d2WorstHour = null;
            $d2MaxScore = -1;
            foreach ($d2Cache['hourly'] ?? [] as $h) {
                if (($h['weighted_score_pct'] ?? 0) > $d2MaxScore) {
                    $d2MaxScore = $h['weighted_score_pct'];
                    $d2WorstHour = $h['iso_time'] ?? null;
                }
            }

            return [
                'available' => true,
                'start_date' => $startDate->format('Y-m-d'),
                'start_date_formatted' => $startDate->format('M d, Y (l)'),
                'end_date' => $endDate->format('Y-m-d'),
                'end_date_formatted' => $endDate->format('M d, Y (l)'),
                'overall_classification' => $overallClass,
                'data_source' => 'Live Open-Meteo Marine & Weather Radar (Anilao, Batangas)',
                'day1' => [
                    'date' => $startDate->format('M d, Y'),
                    'classification' => $day1Class,
                    'recommended_action' => self::MEANING_MAP[$day1Class] ?? 'Proceed with caution.',
                    'worst_hour' => $d1WorstHour ? Carbon::parse($d1WorstHour)->format('g:i A') : '11:00 AM',
                    'lead_time' => max(0, Carbon::now(self::TIMEZONE)->diffInHours($startDate->copy()->setTime(9, 30), false)) . ' hours',
                ],
                'day2' => [
                    'date' => $endDate->format('M d, Y'),
                    'classification' => $day2Class,
                    'recommended_action' => self::MEANING_MAP[$day2Class] ?? 'Proceed with caution.',
                    'worst_hour' => $d2WorstHour ? Carbon::parse($d2WorstHour)->format('g:i A') : '11:00 AM',
                    'lead_time' => max(0, Carbon::now(self::TIMEZONE)->diffInHours($endDate->copy()->setTime(9, 30), false)) . ' hours',
                ],
                'advisory_notes' => [
                    'Forecast models are updated hourly as the scheduled trip approaches.',
                    'Final go/no-go departure decision is subject to Camp FreedivePH operator confirmation.',
                ],
            ];
        }

        // Fallback to Window Evaluation
        $day1AM = $this->assessWindow($startDate->format('Y-m-d'), '09:30', '12:00', 'am');
        $day1PM = $this->assessWindow($startDate->format('Y-m-d'), '15:30', '17:30', 'pm');
        $day1Class = (self::RISK_RANK[$day1PM['classification']] ?? 1) > (self::RISK_RANK[$day1AM['classification']] ?? 1)
            ? $day1PM['classification']
            : $day1AM['classification'];

        $day2AM = $this->assessWindow($endDate->format('Y-m-d'), '09:30', '12:00', 'am');
        $day2PM = $this->assessWindow($endDate->format('Y-m-d'), '15:30', '17:30', 'pm');
        $day2Class = (self::RISK_RANK[$day2PM['classification']] ?? 1) > (self::RISK_RANK[$day2AM['classification']] ?? 1)
            ? $day2PM['classification']
            : $day2AM['classification'];

        $worseRank = max(self::RISK_RANK[$day1Class] ?? 1, self::RISK_RANK[$day2Class] ?? 1);
        $overallClass = array_search($worseRank, self::RISK_RANK) ?: 'Safe';

        $worstHourD1 = (self::RISK_RANK[$day1PM['classification']] ?? 1) > (self::RISK_RANK[$day1AM['classification']] ?? 1)
            ? ($day1PM['worst_hour'] ?: $day1AM['worst_hour'])
            : ($day1AM['worst_hour'] ?: $day1PM['worst_hour']);

        $worstHourD2 = (self::RISK_RANK[$day2PM['classification']] ?? 1) > (self::RISK_RANK[$day2AM['classification']] ?? 1)
            ? ($day2PM['worst_hour'] ?: $day2AM['worst_hour'])
            : ($day2AM['worst_hour'] ?: $day2PM['worst_hour']);

        return [
            'available' => true,
            'start_date' => $startDate->format('Y-m-d'),
            'start_date_formatted' => $startDate->format('M d, Y (l)'),
            'end_date' => $endDate->format('Y-m-d'),
            'end_date_formatted' => $endDate->format('M d, Y (l)'),
            'overall_classification' => $overallClass,
            'data_source' => 'Live Open-Meteo Marine & Weather Radar (Anilao, Batangas)',
            'day1' => [
                'date' => $startDate->format('M d, Y'),
                'classification' => $day1Class,
                'recommended_action' => self::MEANING_MAP[$day1Class] ?? 'Proceed with caution.',
                'worst_hour' => $worstHourD1 ? Carbon::parse($worstHourD1)->format('g:i A') : '11:00 AM',
                'lead_time' => max(0, Carbon::now(self::TIMEZONE)->diffInHours($startDate->copy()->setTime(9, 30), false)) . ' hours',
            ],
            'day2' => [
                'date' => $endDate->format('M d, Y'),
                'classification' => $day2Class,
                'recommended_action' => self::MEANING_MAP[$day2Class] ?? 'Proceed with caution.',
                'worst_hour' => $worstHourD2 ? Carbon::parse($worstHourD2)->format('g:i A') : '11:00 AM',
                'lead_time' => max(0, Carbon::now(self::TIMEZONE)->diffInHours($endDate->copy()->setTime(9, 30), false)) . ' hours',
            ],
            'advisory_notes' => [
                'Forecast models are updated hourly as the scheduled trip approaches.',
                'Final go/no-go departure decision is subject to Camp FreedivePH operator confirmation.',
            ],
        ];
    }

    /**
     * Apply Manual Override (PAGASA-style advisories) to a batch.
     */
    public function applyManualOverride(Batch $batch, array $overrideData, User $operator, bool $cancelBatch = false, ?string $cancelReason = null): array
    {
        return DB::transaction(function () use ($batch, $overrideData, $operator, $cancelBatch, $cancelReason) {
            $isOverrideActive = $this->checkOverrideConditions($overrideData);

            $overrideRecord = ManualOverride::create([
                'batch_id' => $batch->id,
                'tcws_signal' => (int) ($overrideData['tcws_signal'] ?? 0),
                'gale_warning' => (bool) ($overrideData['gale_warning'] ?? false),
                'thunderstorm_advisory' => (bool) ($overrideData['thunderstorm_advisory'] ?? false),
                'typhoon_within_distance' => (bool) ($overrideData['typhoon_within_distance'] ?? false),
                'tsunami_warning' => (bool) ($overrideData['tsunami_warning'] ?? false),
                'reason' => $overrideData['reason'] ?? 'PAGASA Marine Weather Advisory',
                'cancelled_batch' => $cancelBatch,
                'applied_by' => $operator->id,
                'created_at' => now(),
            ]);

            // Re-run batch assessment with override flags
            $assessmentResult = $this->assessBatch($batch, $overrideData, $operator);

            // Cancel batch if requested
            if ($cancelBatch && $isOverrideActive) {
                $reasonText = $cancelReason ?: ($overrideData['reason'] ?: 'Camp cancellation due to active PAGASA severe weather advisory');
                $this->cancelBatchWithRefundsAndNotifications($batch, $reasonText, $operator);
            }

            AuditLogger::log(
                'MANUAL_OVERRIDE_APPLIED',
                "Manual weather override applied to batch {$batch->batch_code}. Critical Risk forced. Cancelled=" . ($cancelBatch ? 'Yes' : 'No'),
                $operator,
                $operator->name
            );

            return [
                'override' => $overrideRecord,
                'assessment' => $assessmentResult,
            ];
        });
    }

    /**
     * Cancel whole batch, trigger 100% force majeure refunds, and dispatch templated emails.
     */
    public function cancelBatchWithRefundsAndNotifications(Batch $batch, string $cancellationReason, User $operator): int
    {
        $batch->update([
            'status' => 'cancelled_by_camp',
            'lifecycle_status' => 'cancelled_by_camp',
            'cancelled_at' => now(),
            'cancellation_reason' => $cancellationReason,
        ]);

        BatchStatusLog::create([
            'batch_id' => $batch->id,
            'old_status' => $batch->status,
            'new_status' => 'cancelled_by_camp',
            'changed_by' => $operator->id,
            'note' => "Cancelled by Camp due to weather safety advisory: {$cancellationReason}",
        ]);

        $connectedBookings = $batch->bookings()
            ->whereNotIn('status', ['cancelled_by_guest', 'no_show'])
            ->get();

        $notificationsSent = 0;

        foreach ($connectedBookings as $booking) {
            $bookingOldStatus = $booking->status;
            $booking->update(['status' => 'cancelled_by_camp']);

            // Auto-trigger 100% force majeure refund eligibility
            foreach ($booking->payments()->where('status', 'completed')->get() as $payment) {
                RefundRequest::create([
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'requested_by' => 'camp_force_majeure',
                    'requested_at' => now(),
                    'eligibility_calculated' => [
                        'days_until_dive' => max(0, Carbon::now()->diffInDays($booking->start_date, false)),
                        'eligible_for_refund' => true,
                        'refund_percentage' => 100,
                        'window_label' => 'Camp Cancellation (100% Force Majeure Refund)',
                        'policy_action_text' => 'Camp cancelled batch due to marine safety / weather. Full refund approved.',
                    ],
                    'status' => 'pending',
                    'notes' => "Weather cancellation for batch {$batch->batch_code}: {$cancellationReason}",
                ]);
            }

            // Generate Templated Cancellation Message (PRD Section 9)
            $scheduledDateStr = $booking->start_date->format('M d, Y') . ' – ' . $booking->end_date->format('M d, Y');
            $messageBody = "Good day, {$booking->contact_name}. Your scheduled date for {$scheduledDateStr} will be canceled due to:\n\n- {$cancellationReason}\n\nThere will be options for this cancelled schedule:\n- Full refund\n- Reschedule\n\nYou can select your preferred option by entering your booking number ({$booking->booking_number}) and PIN in Manage Booking.";

            NotificationLog::create([
                'batch_id' => $batch->id,
                'booking_id' => $booking->id,
                'recipient_email' => $booking->contact_email,
                'recipient_name' => $booking->contact_name,
                'subject' => "Camp FreedivePH Schedule Cancellation Notice - {$scheduledDateStr}",
                'message_body' => $messageBody,
                'channel' => 'email',
                'sent_by' => $operator->id,
                'sent_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $bookingOldStatus,
                'new_status' => 'cancelled_by_camp',
                'changed_by' => $operator->id,
                'note' => "Cancelled by Camp due to weather safety. Notification sent to {$booking->contact_email}.",
            ]);

            $notificationsSent++;
        }

        return $notificationsSent;
    }

    /**
     * Check if any manual override condition is active.
     */
    public function checkOverrideConditions(?array $overrides): bool
    {
        if (empty($overrides)) {
            return false;
        }

        $tcwsSignal = (int) ($overrides['tcws_signal'] ?? 0);
        $galeWarning = (bool) ($overrides['gale_warning'] ?? false);
        $thunderstorm = (bool) ($overrides['thunderstorm_advisory'] ?? false);
        $typhoon = (bool) ($overrides['typhoon_within_distance'] ?? false);
        $tsunami = (bool) ($overrides['tsunami_warning'] ?? false);

        return ($tcwsSignal >= 3 || $galeWarning || $thunderstorm || $typhoon || $tsunami);
    }

    /**
     * Assess a single open water window (e.g. 09:30-12:00 or 15:30-17:30).
     */
    protected function assessWindow(string $plannedDate, string $diveStart, string $diveEnd, string $windowType, ?array $overrides = null): array
    {
        $overrideTriggered = $this->checkOverrideConditions($overrides);

        // Optional Python FastAPI microservice integration if explicitly configured
        if (config('services.forecast_engine.enabled', false)) {
            try {
                $serviceUrl = config('services.forecast_engine.url', 'http://127.0.0.1:8001');
                $response = Http::timeout(1)->post("{$serviceUrl}/assess-booking", [
                    'planned_date' => $plannedDate,
                    'dive_start' => $diveStart,
                    'dive_end' => $diveEnd,
                    'overrides' => $overrides,
                    'tide_score' => 0,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatEngineWindowResponse($data, $windowType, $overrideTriggered);
                }
            } catch (Exception $e) {
                // Fall back immediately to native cached evaluator
            }
        }

        // Native live Open-Meteo & sub-millisecond cached scoring pipeline
        return $this->evaluateWindowNatively($plannedDate, $diveStart, $diveEnd, $windowType, $overrides);
    }

    /**
     * Native evaluation pipeline using live Open-Meteo APIs and exact Python scoring rules.
     */
    protected function evaluateWindowNatively(string $plannedDate, string $diveStart, string $diveEnd, string $windowType, ?array $overrides): array
    {
        $overrideTriggered = $this->checkOverrideConditions($overrides);

        // AM 09:30-12:00 -> 10:00, 11:00, 12:00
        // PM 15:30-17:30 -> 16:00, 17:00
        $hours = $windowType === 'am' ? [10, 11, 12] : [16, 17];
        
        $hourlyList = [];
        $maxScore = -1;
        $worstHour = null;

        // Fetch live marine & weather data from Open-Meteo
        $marineData = $this->fetchOpenMeteoMarine($plannedDate);
        $weatherData = $this->fetchOpenMeteoWeather($plannedDate);

        foreach ($hours as $hour) {
            $timeStr = sprintf('%sT%02d:00:00+08:00', $plannedDate, $hour);
            $idx = $hour; // Index in hourly array

            $waveHeight = isset($marineData['wave_height'][$idx]) ? (float)$marineData['wave_height'][$idx] : 0.70;
            $wavePeriod = isset($marineData['wave_period'][$idx]) ? (float)$marineData['wave_period'][$idx] : 6.10;
            $swellHeight = isset($marineData['swell_wave_height'][$idx]) ? (float)$marineData['swell_wave_height'][$idx] : 0.60;
            $windWaveHeight = isset($marineData['wind_wave_height'][$idx]) ? (float)$marineData['wind_wave_height'][$idx] : 0.35;
            
            // Open-Meteo ocean current velocity in km/h -> convert to m/s
            $rawCurrent = isset($marineData['ocean_current_velocity'][$idx]) ? (float)$marineData['ocean_current_velocity'][$idx] : 1.1;
            $oceanCurrent = round($rawCurrent * 0.27778, 2);

            $precip = isset($weatherData['precipitation'][$idx]) ? (float)$weatherData['precipitation'][$idx] : 0.0;
            $rawRain = isset($weatherData['rain'][$idx]) ? (float)$weatherData['rain'][$idx] : 0.0;
            $showers = isset($weatherData['showers'][$idx]) ? (float)$weatherData['showers'][$idx] : 0.0;
            $rain = max($precip, $rawRain, $showers);

            $pressure = isset($weatherData['pressure_msl'][$idx]) ? (float)$weatherData['pressure_msl'][$idx] : 1010.5;
            $rawWindSpeed = isset($weatherData['wind_speed_10m'][$idx]) ? (float)$weatherData['wind_speed_10m'][$idx] : 12.5;
            $windGusts = isset($weatherData['wind_gusts_10m'][$idx]) ? (float)$weatherData['wind_gusts_10m'][$idx] : 0.0;
            $windSpeed = max($rawWindSpeed, $windGusts * 0.75);

            $windDir = isset($weatherData['wind_direction_10m'][$idx]) ? (float)$weatherData['wind_direction_10m'][$idx] : 245.0;

            // Deterministic Physical Hard-Gates (PCG Small Craft / Marine Ceilings)
            $isPhysicalBreach = (
                $rawWindSpeed >= 38.0 || $windGusts >= 48.0 ||
                $waveHeight >= 1.80 || $swellHeight >= 1.80 ||
                $oceanCurrent >= 0.80 ||
                $rain >= 25.0 ||
                $pressure <= 998.0
            );

            // Score variables (0-4)
            $scores = [
                'wave_height' => $this->scoreWaveHeight($waveHeight),
                'wind_speed' => $this->scoreWindSpeed($rawWindSpeed, $windGusts),
                'ocean_current' => $this->scoreOceanCurrent($oceanCurrent),
                'swell_height' => $this->scoreSwellHeight($swellHeight),
                'wave_period' => $this->scoreWavePeriod($wavePeriod),
                'wind_wave_height' => $this->scoreWindWaveHeight($windWaveHeight),
                'rain' => $this->scoreRain($rain),
                'sea_level_pressure' => $this->scoreSeaLevelPressure($pressure),
                'tide_height' => 0, // placeholder
                'wind_direction' => $this->scoreWindDirection($windDir),
            ];

            $weightedScorePct = $isPhysicalBreach ? 100.0 : $this->computeWeightedScore($scores);
            $hourClass = ($overrideTriggered || $isPhysicalBreach) ? 'Critical Risk' : $this->classifyScore($weightedScorePct);

            if ($weightedScorePct > $maxScore) {
                $maxScore = $weightedScorePct;
                $worstHour = $timeStr;
            }

            $hourlyList[] = [
                'forecast_time' => Carbon::parse($timeStr),
                'wave_height' => $waveHeight,
                'wave_period' => $wavePeriod,
                'swell_height' => $swellHeight,
                'wind_wave_height' => $windWaveHeight,
                'ocean_current' => $oceanCurrent,
                'rain' => $rain,
                'sea_level_pressure' => $pressure,
                'wind_speed' => $windSpeed,
                'wind_direction' => $windDir,
                'tide_height' => 0.0,
                'tide_score' => 0,
                'weighted_score_pct' => $overrideTriggered ? null : $weightedScorePct,
                'classification' => $hourClass,
                'recommended_action' => self::MEANING_MAP[$hourClass] ?? 'Proceed with caution.',
                'is_worst_hour_in_window' => false,
            ];
        }

        // Mark worst hour
        foreach ($hourlyList as &$item) {
            if ($item['forecast_time']->format('Y-m-d\TH:i:sP') === $worstHour || count($hourlyList) === 1) {
                $item['is_worst_hour_in_window'] = true;
            }
        }

        $windowClass = $overrideTriggered ? 'Critical Risk' : $this->classifyScore($maxScore);

        return [
            'planned_date' => $plannedDate,
            'window_type' => $windowType,
            'dive_start' => $diveStart,
            'dive_end' => $diveEnd,
            'classification' => $windowClass,
            'weighted_score_pct' => $overrideTriggered ? null : $maxScore,
            'worst_hour' => $worstHour,
            'hourly' => $hourlyList,
        ];
    }

    protected function formatEngineWindowResponse(array $data, string $windowType, bool $overrideTriggered): array
    {
        $hourlyList = [];
        $worstHour = $data['worst_hour'] ?? null;
        $assessment = $data['assessment'] ?? [];

        foreach ($data['hourly_assessments'] ?? [] as $h) {
            $forecasts = $h['forecasts'] ?? [];
            $hourlyList[] = [
                'forecast_time' => Carbon::parse($h['timestamp']),
                'wave_height' => $forecasts['wave_height'] ?? 0.70,
                'wave_period' => $forecasts['wave_period'] ?? 6.10,
                'swell_height' => $forecasts['swell_height'] ?? 0.60,
                'wind_wave_height' => $forecasts['wind_wave_height'] ?? 0.35,
                'ocean_current' => $forecasts['ocean_current'] ?? 0.70,
                'rain' => $forecasts['rain'] ?? 0.0,
                'sea_level_pressure' => $forecasts['sea_level_pressure'] ?? 1010.5,
                'wind_speed' => $forecasts['wind_speed'] ?? 12.0,
                'wind_direction' => $forecasts['wind_direction'] ?? 245.0,
                'tide_height' => $forecasts['tide_height'] ?? 0.0,
                'tide_score' => 0,
                'weighted_score_pct' => $overrideTriggered ? null : ($h['weighted_score_pct'] ?? 18.0),
                'classification' => $overrideTriggered ? 'Critical Risk' : ($h['classification'] ?? 'Safe'),
                'recommended_action' => self::MEANING_MAP[$overrideTriggered ? 'Critical Risk' : ($h['classification'] ?? 'Safe')],
                'is_worst_hour_in_window' => $h['timestamp'] === $worstHour,
            ];
        }

        return [
            'planned_date' => $data['planned_date'] ?? '',
            'window_type' => $windowType,
            'dive_start' => $data['dive_start'] ?? '',
            'dive_end' => $data['dive_end'] ?? '',
            'classification' => $overrideTriggered ? 'Critical Risk' : ($assessment['classification'] ?? 'Safe'),
            'weighted_score_pct' => $overrideTriggered ? null : ($assessment['weighted_score_pct'] ?? 18.0),
            'worst_hour' => $worstHour,
            'hourly' => $hourlyList,
        ];
    }

    /**
     * Master cache updater: pulls 16-day continuous 24-hour marine and weather forecasts from Open-Meteo
     * and saves structured continuous data in cache for sub-millisecond lookups.
     */
    public function updateAllForecasts(int $forecastDays = 16): array
    {
        $today = Carbon::today(self::TIMEZONE);
        $startDateStr = $today->format('Y-m-d');
        $endDateStr = $today->copy()->addDays($forecastDays - 1)->format('Y-m-d');

        // 1. Fetch 16-Day Marine Forecast in a single API call
        $marineRes = Http::timeout(10)->withoutVerifying()->get('https://marine-api.open-meteo.com/v1/marine', [
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'timezone' => self::TIMEZONE,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'hourly' => 'wave_height,wave_period,swell_wave_height,wind_wave_height,ocean_current_velocity',
        ]);
        $marineHourly = $marineRes->successful() ? ($marineRes->json()['hourly'] ?? []) : [];

        // 2. Fetch 16-Day Atmospheric Weather Forecast in a single API call
        $weatherRes = Http::timeout(10)->withoutVerifying()->get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'timezone' => self::TIMEZONE,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'hourly' => 'precipitation,rain,showers,pressure_msl,wind_speed_10m,wind_gusts_10m,wind_direction_10m',
        ]);
        $weatherHourly = $weatherRes->successful() ? ($weatherRes->json()['hourly'] ?? []) : [];

        if (empty($marineHourly) && empty($weatherHourly)) {
            throw new Exception("Unable to communicate with Open-Meteo forecast servers.");
        }

        // 3. Build Day-by-Day 24-Hour Continuous Profiles
        $times = $marineHourly['time'] ?? $weatherHourly['time'] ?? [];
        $dayBuckets = [];
        $dailySummaries = [];

        foreach ($times as $index => $isoTime) {
            $dt = Carbon::parse($isoTime);
            $dateKey = $dt->format('Y-m-d');
            $hour = $dt->hour;

            if (!isset($dayBuckets[$dateKey])) {
                $dayBuckets[$dateKey] = [
                    'date' => $dateKey,
                    'marine' => [
                        'time' => [],
                        'wave_height' => [],
                        'wave_period' => [],
                        'swell_wave_height' => [],
                        'wind_wave_height' => [],
                        'ocean_current_velocity' => [],
                    ],
                    'weather' => [
                        'time' => [],
                        'rain' => [],
                        'pressure_msl' => [],
                        'wind_speed_10m' => [],
                        'wind_direction_10m' => [],
                    ],
                    'hourly_scores' => [],
                ];
            }

            // Populate marine array
            $dayBuckets[$dateKey]['marine']['time'][] = $isoTime;
            $wHeight = (float) ($marineHourly['wave_height'][$index] ?? 0.7);
            $wPeriod = (float) ($marineHourly['wave_period'][$index] ?? 6.1);
            $sHeight = (float) ($marineHourly['swell_wave_height'][$index] ?? 0.6);
            $wwHeight = (float) ($marineHourly['wind_wave_height'][$index] ?? 0.35);
            $rawCurrent = (float) ($marineHourly['ocean_current_velocity'][$index] ?? 1.1);

            $dayBuckets[$dateKey]['marine']['wave_height'][] = $wHeight;
            $dayBuckets[$dateKey]['marine']['wave_period'][] = $wPeriod;
            $dayBuckets[$dateKey]['marine']['swell_wave_height'][] = $sHeight;
            $dayBuckets[$dateKey]['marine']['wind_wave_height'][] = $wwHeight;
            $dayBuckets[$dateKey]['marine']['ocean_current_velocity'][] = $rawCurrent;

            // Populate weather array
            $dayBuckets[$dateKey]['weather']['time'][] = $isoTime;
            $precipVal = (float) ($weatherHourly['precipitation'][$index] ?? 0.0);
            $rawRainVal = (float) ($weatherHourly['rain'][$index] ?? 0.0);
            $showersVal = (float) ($weatherHourly['showers'][$index] ?? 0.0);
            $rainVal = max($precipVal, $rawRainVal, $showersVal);

            $pressVal = (float) ($weatherHourly['pressure_msl'][$index] ?? 1010.5);
            $rawWindSpd = (float) ($weatherHourly['wind_speed_10m'][$index] ?? 12.0);
            $windGustsVal = (float) ($weatherHourly['wind_gusts_10m'][$index] ?? 0.0);
            $windSpd = max($rawWindSpd, $windGustsVal * 0.75);
            $windDir = (float) ($weatherHourly['wind_direction_10m'][$index] ?? 245.0);

            $dayBuckets[$dateKey]['weather']['rain'][] = $rainVal;
            $dayBuckets[$dateKey]['weather']['pressure_msl'][] = $pressVal;
            $dayBuckets[$dateKey]['weather']['wind_speed_10m'][] = $windSpd;
            $dayBuckets[$dateKey]['weather']['wind_direction_10m'][] = $windDir;

            // Calculate hour safety score (0-100%)
            $oceanCurrent = round($rawCurrent * 0.27778, 2);

            // Deterministic Physical Hard-Gates (PCG Small Craft / Marine Ceilings)
            $isPhysicalBreach = (
                $rawWindSpd >= 38.0 || $windGustsVal >= 48.0 ||
                $wHeight >= 1.80 || $sHeight >= 1.80 ||
                $oceanCurrent >= 0.80 ||
                $rainVal >= 25.0 ||
                $pressVal <= 998.0
            );

            $scores = [
                'wave_height' => $this->scoreWaveHeight($wHeight),
                'wind_speed' => $this->scoreWindSpeed($rawWindSpd, $windGustsVal),
                'ocean_current' => $this->scoreOceanCurrent($oceanCurrent),
                'swell_height' => $this->scoreSwellHeight($sHeight),
                'wave_period' => $this->scoreWavePeriod($wPeriod),
                'wind_wave_height' => $this->scoreWindWaveHeight($wwHeight),
                'rain' => $this->scoreRain($rainVal),
                'sea_level_pressure' => $this->scoreSeaLevelPressure($pressVal),
                'tide_height' => 0,
                'wind_direction' => $this->scoreWindDirection($windDir),
            ];

            $weightedScorePct = $isPhysicalBreach ? 100.0 : $this->computeWeightedScore($scores);
            $hourClass = $isPhysicalBreach ? 'Critical Risk' : $this->classifyScore($weightedScorePct);

            $dayBuckets[$dateKey]['hourly_scores'][$hour] = [
                'hour' => $hour,
                'iso_time' => $isoTime,
                'weighted_score_pct' => $weightedScorePct,
                'classification' => $hourClass,
                'wave_height' => $wHeight,
                'wave_period' => $wPeriod,
                'swell_height' => $sHeight,
                'ocean_current' => $oceanCurrent,
                'wind_wave_height' => $wwHeight,
                'rain' => $rainVal,
                'sea_level_pressure' => $pressVal,
                'wind_speed' => $windSpd,
                'wind_direction' => $windDir,
                'tide_height' => 0.0,
            ];
        }

        // 4. Cache each day individually and summarize metrics
        foreach ($dayBuckets as $dateKey => $bucket) {
            Cache::put("forecast:marine_cache:{$dateKey}", $bucket['marine'], now()->addMinutes(60));
            Cache::put("forecast:weather_cache:{$dateKey}", $bucket['weather'], now()->addMinutes(60));

            $scores24h = array_column($bucket['hourly_scores'], 'weighted_score_pct');
            $maxScore24h = !empty($scores24h) ? max($scores24h) : 0.0;
            $overall24hClass = $this->classifyScore($maxScore24h);

            $daytimeScores = [];
            for ($h = 6; $h <= 18; $h++) {
                if (isset($bucket['hourly_scores'][$h])) {
                    $daytimeScores[] = $bucket['hourly_scores'][$h]['weighted_score_pct'];
                }
            }
            $maxDaytimeScore = !empty($daytimeScores) ? max($daytimeScores) : $maxScore24h;
            $daytimeClass = $this->classifyScore($maxDaytimeScore);

            $amScores = array_filter($bucket['hourly_scores'], fn($i) => in_array($i['hour'], [10, 11, 12]));
            $amMax = !empty($amScores) ? max(array_column($amScores, 'weighted_score_pct')) : 0.0;
            $amClass = $this->classifyScore($amMax);

            $pmScores = array_filter($bucket['hourly_scores'], fn($i) => in_array($i['hour'], [16, 17]));
            $pmMax = !empty($pmScores) ? max(array_column($pmScores, 'weighted_score_pct')) : 0.0;
            $pmClass = $this->classifyScore($pmMax);

            $waves = $bucket['marine']['wave_height'];
            $winds = $bucket['weather']['wind_speed_10m'];

            $summary = [
                'date' => $dateKey,
                'overall_classification' => $overall24hClass,
                'overall_score_pct' => $maxScore24h,
                'daytime_classification' => $daytimeClass,
                'daytime_score_pct' => $maxDaytimeScore,
                'am_classification' => $amClass,
                'pm_classification' => $pmClass,
                'avg_wave_height' => !empty($waves) ? round(array_sum($waves) / count($waves), 2) : 0.0,
                'max_wave_height' => !empty($waves) ? max($waves) : 0.0,
                'avg_wind_speed' => !empty($winds) ? round(array_sum($winds) / count($winds), 1) : 0.0,
                'max_wind_speed' => !empty($winds) ? max($winds) : 0.0,
                'hourly' => $bucket['hourly_scores'],
            ];

            Cache::put("forecast:date:{$dateKey}", $summary, now()->addMinutes(60));
            $dailySummaries[$dateKey] = $summary;
        }

        // 5. Store Master 16-Day Cache and Update Timestamp
        $masterData = [
            'updated_at' => now(self::TIMEZONE)->toIso8601String(),
            'days_cached' => count($dailySummaries),
            'daily_summaries' => $dailySummaries,
            'source' => 'Open-Meteo Best Match (ECMWF + GFS + CMEMS)',
        ];

        Cache::put('forecast:continuous_16d', $masterData, now()->addMinutes(60));
        Cache::put('forecast:last_updated_at', now(self::TIMEZONE)->toDateTimeString(), now()->addMinutes(60));

        return $masterData;
    }

    /**
     * Retrieve pre-cached 24-hour forecast for a specific date if available.
     */
    public function getCachedDayForecast(string $date): ?array
    {
        return Cache::get("forecast:date:{$date}");
    }

    /**
     * Fetch Open-Meteo Marine Forecast with cache-first lookup and error tolerance.
     */
    protected function fetchOpenMeteoMarine(string $date): array
    {
        $cached = Cache::get("forecast:marine_cache:{$date}");
        if (!empty($cached)) {
            return $cached;
        }

        try {
            $res = Http::timeout(6)->withoutVerifying()->get('https://marine-api.open-meteo.com/v1/marine', [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'timezone' => self::TIMEZONE,
                'start_date' => $date,
                'end_date' => $date,
                'hourly' => 'wave_height,wave_period,swell_wave_height,wind_wave_height,ocean_current_velocity',
            ]);

            if ($res->successful()) {
                $data = $res->json()['hourly'] ?? [];
                Cache::put("forecast:marine_cache:{$date}", $data, now()->addMinutes(30));
                return $data;
            }
        } catch (Exception $e) {
            Log::warning("Open-Meteo marine call failed: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Fetch Open-Meteo Weather Forecast with cache-first lookup and error tolerance.
     */
    protected function fetchOpenMeteoWeather(string $date): array
    {
        $cached = Cache::get("forecast:weather_cache:{$date}");
        if (!empty($cached)) {
            return $cached;
        }

        try {
            $res = Http::timeout(6)->withoutVerifying()->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'timezone' => self::TIMEZONE,
                'start_date' => $date,
                'end_date' => $date,
                'hourly' => 'precipitation,rain,showers,pressure_msl,wind_speed_10m,wind_gusts_10m,wind_direction_10m',
            ]);

            if ($res->successful()) {
                $data = $res->json()['hourly'] ?? [];
                Cache::put("forecast:weather_cache:{$date}", $data, now()->addMinutes(30));
                return $data;
            }
        } catch (Exception $e) {
            Log::warning("Open-Meteo weather call failed: " . $e->getMessage());
        }

        return [];
    }

    // =========================================================================
    // Rule-Based Threshold Scoring Functions (0-4)
    // =========================================================================

    protected function scoreWaveHeight(float $v): int
    {
        if ($v < 0.30) return 0;
        if ($v < 0.50) return 1;
        if ($v < 0.80) return 2;
        if ($v < 1.00) return 3;
        return 4;
    }

    protected function scoreSwellHeight(float $v): int
    {
        return $this->scoreWaveHeight($v);
    }

    protected function scoreWindWaveHeight(float $v): int
    {
        if ($v < 0.20) return 0;
        if ($v < 0.40) return 1;
        if ($v < 0.60) return 2;
        if ($v < 0.80) return 3;
        return 4;
    }

    protected function scoreWavePeriod(float $v): int
    {
        if ($v > 7.0) return 0;
        if ($v > 5.0) return 1;
        if ($v > 3.0) return 2;
        if ($v > 2.0) return 3;
        return 4;
    }

    protected function scoreOceanCurrent(float $v): int
    {
        if ($v < 0.10) return 0;
        if ($v < 0.30) return 1;
        if ($v < 0.50) return 2;
        if ($v < 0.80) return 3;
        return 4;
    }

    protected function scoreWindSpeed(float $sustained, float $gusts): int
    {
        // Evaluates sustained wind and sudden squall gusts (PCG Small Craft / Banca criteria)
        if ($sustained < 12.0 && $gusts < 18.0) return 0;
        if ($sustained < 20.0 && $gusts < 28.0) return 1;
        if ($sustained < 28.0 && $gusts < 38.0) return 2;
        if ($sustained < 38.0 && $gusts < 48.0) return 3;
        return 4;
    }

    protected function scoreRain(float $v): int
    {
        // Tightened bounds: 0.2mm captures tropical monsoon mist and convective drizzle
        if ($v < 0.2) return 0;
        if ($v < 2.5) return 1;
        if ($v < 7.5) return 2;
        if ($v < 20.0) return 3;
        return 4;
    }

    protected function scoreSeaLevelPressure(float $v): int
    {
        // Fixed boundary inequality logic to eliminate gaps
        if ($v >= 1012.0) return 0;
        if ($v >= 1008.0) return 1;
        if ($v >= 1004.0) return 2;
        if ($v >= 1000.0) return 3;
        return 4;
    }

    protected function scoreWindDirection(float $deg): int
    {
        if (($deg >= 0 && $deg <= 90) || ($deg > 315 && $deg <= 360)) return 0;
        if ($deg <= 135) return 1;
        if ($deg <= 180 || $deg > 270) return 2;
        return 3;
    }

    protected function computeWeightedScore(array $scores): float
    {
        $maxPossible = 4.0 * array_sum(self::WEIGHTS);
        $weightedSum = 0.0;
        foreach (self::WEIGHTS as $var => $weight) {
            $weightedSum += ($scores[$var] ?? 0) * $weight;
        }

        $rawScorePct = ($weightedSum / $maxPossible) * 100.0;

        // Compound Risk Synergy: Exponential penalty when multiple hazards co-occur
        $highRiskFactors = 0;
        if (($scores['wave_height'] ?? 0) >= 2) $highRiskFactors++;
        if (($scores['wind_speed'] ?? 0) >= 2) $highRiskFactors++;
        if (($scores['ocean_current'] ?? 0) >= 2) $highRiskFactors++;
        if (($scores['wind_direction'] ?? 0) >= 2) $highRiskFactors++;
        if (($scores['wave_period'] ?? 0) >= 2) $highRiskFactors++;

        $synergyMultiplier = 1.0;
        if ($highRiskFactors >= 4) {
            $synergyMultiplier = 1.25; // 25% compound multiplier for multi-hazard conditions
        } elseif ($highRiskFactors >= 3) {
            $synergyMultiplier = 1.15; // 15% compound multiplier
        }

        return min(100.0, round($rawScorePct * $synergyMultiplier, 1));
    }

    protected function classifyScore(float $weightedPct): string
    {
        if ($weightedPct <= 20) return 'Very Safe';
        if ($weightedPct <= 40) return 'Safe';
        if ($weightedPct <= 60) return 'Moderate';
        if ($weightedPct <= 80) return 'High Risk';
        return 'Critical Risk';
    }
}
