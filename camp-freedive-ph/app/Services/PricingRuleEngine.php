<?php

namespace App\Services;

use App\Models\BookingParticipant;
use App\Models\PricingRule;
use Carbon\Carbon;

/**
 * Dynamic Yield Management & Pricing Rule Engine.
 *
 * Business Model & Economic Rationale:
 * 1. Multi-Factor Dynamic Pricing: Evaluates seasonal trends, occupancy velocity, ML demand forecasts,
 *    and booking lead times to optimize freediving camp capacity utilization across the year.
 * 2. Predictive Yield Management: Connects with Prophet/XGBoost seasonal demand forecasts to project
 *    batch fill rates and optimize early-bird discounts and capacity revenue ahead of time.
 * 3. Strict ±30% Price Clamping Cap (ADJUSTMENT_PERCENTAGE_CAP):
 *    Protects customer trust and transparent pricing by strictly bounding cumulative discounts/surcharges
 *    between -30% and +30% of the base class tier price.
 * 4. Batangas Micro-Climate Seasonality:
 *    - Peak (Nov - Apr): Amihan northeast monsoon delivers dry, calm conditions and peak tourism demand.
 *    - Shoulder (May, Oct): Monsoon transitional months with moderate occupancy.
 *    - Off-Peak (Jun - Sep): Habagat southwest monsoon brings wet weather; discounts incentivize advance bookings.
 */
class PricingRuleEngine
{
    /**
     * Percentage cap to clamp stacked adjustments (+/- 30% of base price).
     */
    public const ADJUSTMENT_PERCENTAGE_CAP = 0.30;

    /**
     * Standard Base Prices per class type (fallback defaults).
     */
    public const BASE_PRICES = [
        'discovery' => 4250.00,
        'fundive_certified' => 2500.00,
        'fundive_non_certified' => 3300.00,
        'refinement' => 4100.00,
    ];

    public function __construct(
        protected ?DemandForecastService $demandForecastService = null,
        protected ?SystemSettingService $settingService = null
    ) {
        $this->demandForecastService ??= app(DemandForecastService::class);
        $this->settingService ??= app(SystemSettingService::class);
    }

    /**
     * Resolve base price for given class and diver certification.
     */
    public function getBasePrice(string $classType, bool $isCertified = false): float
    {
        $classKey = strtolower($classType);
        if ($classKey === 'fundive') {
            return $isCertified
                ? (float) ($this->settingService?->get('program_pricing.base_price_fundive_cert', self::BASE_PRICES['fundive_certified']) ?? self::BASE_PRICES['fundive_certified'])
                : (float) ($this->settingService?->get('program_pricing.base_price_fundive_noncert', self::BASE_PRICES['fundive_non_certified']) ?? self::BASE_PRICES['fundive_non_certified']);
        }

        $settingKey = match ($classKey) {
            'discovery' => 'program_pricing.base_price_discovery',
            'refinement' => 'program_pricing.base_price_refinement',
            default => null,
        };

        if ($settingKey) {
            $val = $this->settingService?->get($settingKey);
            if ($val !== null) {
                return (float) $val;
            }
        }

        return self::BASE_PRICES[$classKey] ?? self::BASE_PRICES['discovery'];
    }

    /**
     * Get dynamic pricing cap as a decimal float (e.g. 0.30 for 30%).
     */
    public function getAdjustmentCap(): float
    {
        $capPercent = $this->settingService?->get('program_pricing.dynamic_pricing_cap_percent');
        if ($capPercent !== null && is_numeric($capPercent)) {
            return (float) $capPercent / 100.0;
        }

        return self::ADJUSTMENT_PERCENTAGE_CAP;
    }

    /**
     * Determine calendar season for a date in Batangas, factoring in ML micro-climate forecasts:
     * - Peak: November to April (Months 11, 12, 1, 2, 3, 4)
     * - Shoulder: May and October (Months 5, 10)
     * - Off-Peak: June to September (Months 6, 7, 8, 9)
     */
    public function getSeasonForDate(string|Carbon $date): string
    {
        // 1. Check if ML demand forecast identifies a specific micro-climate season period
        if ($this->demandForecastService) {
            $forecast = $this->demandForecastService->getForecastForDate($date);
            if (!empty($forecast['season_period'])) {
                $period = strtolower(str_replace(['-', ' '], '_', $forecast['season_period']));
                if (in_array($period, ['peak', 'shoulder', 'off_peak'], true)) {
                    return $period;
                }
            }
        }

        // 2. Fallback to Batangas monsoon calendar
        $d = Carbon::parse($date);
        $month = (int) $d->format('n');

        if (in_array($month, [11, 12, 1, 2, 3, 4], true)) {
            return 'peak';
        }

        if (in_array($month, [5, 10], true)) {
            return 'shoulder';
        }

        return 'off_peak';
    }

    /**
     * Calculate live dynamic demand level for a date combining:
     * 1. Current physical bookings in DB
     * 2. Forward-looking Prophet/XGBoost seasonal demand forecasts
     *
     * Classification:
     * - High: > 60% capacity (> 27 pax of 45) or ML predicted high volume
     * - Medium: 25% - 60% capacity (11 to 27 pax) or ML predicted medium volume
     * - Low: < 25% capacity (< 11 pax) and ML predicted low volume
     */
    public function getDemandForDate(string|Carbon $date): string
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');

        // Current actual confirmed headcount in DB
        $bookedCount = BookingParticipant::whereHas('booking', function ($q) use ($dateStr) {
            $q->whereDate('start_date', $dateStr)
              ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment']);
        })->count();

        $actualOccupancyRate = $bookedCount / 45.0;

        // If actual bookings already reached high capacity, honor that immediately
        if ($actualOccupancyRate >= 0.60) {
            return 'high';
        }

        // Check forward-looking ML Prophet/XGBoost demand forecast
        if ($this->demandForecastService) {
            $forecast = $this->demandForecastService->getForecastForDate($date);
            if ($forecast) {
                $mlDemand = strtolower($forecast['demand_level'] ?? '');
                $predictedPax = (float) ($forecast['predicted_participants'] ?? 0);
                $predictedOccupancyRate = $predictedPax / 45.0;

                $effectiveOccupancy = max($actualOccupancyRate, $predictedOccupancyRate);

                if ($effectiveOccupancy >= 0.60 || $mlDemand === 'high') {
                    return 'high';
                }

                if ($effectiveOccupancy >= 0.25 || $mlDemand === 'medium') {
                    return 'medium';
                }

                return 'low';
            }
        }

        if ($actualOccupancyRate >= 0.25) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Days between today and the scheduled dive date.
     */
    public function getLeadTimeDays(string|Carbon $date): int
    {
        $today = Carbon::now('Asia/Manila')->startOfDay();
        $target = Carbon::parse($date)->startOfDay();

        return (int) $today->diffInDays($target, false);
    }

    /**
     * Evaluate all matching active pricing rules for a class and date.
     * Enforces priority ordering and a +/- 30% clamping cap.
     */
    public function evaluate(string $classType, string|Carbon $diveDate, bool $isCertified = false, int $paxCount = 1): array
    {
        $basePrice = $this->getBasePrice($classType, $isCertified);
        $normalizedClass = strtolower($classType);
        $season = $this->getSeasonForDate($diveDate);
        $demand = $this->getDemandForDate($diveDate);
        $leadTimeDays = $this->getLeadTimeDays($diveDate);

        // Fetch matching active rules
        $rules = PricingRule::active()
            ->where(function ($q) use ($normalizedClass) {
                $q->where('applies_to', 'all')
                  ->orWhere('applies_to', $normalizedClass);
            })
            ->orderBy('priority', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $appliedAdjustments = [];
        $rawTotalDelta = 0.0;

        foreach ($rules as $rule) {
            $matched = false;

            switch ($rule->rule_type) {
                case 'demand':
                    $matched = ($rule->condition_value === $demand);
                    break;

                case 'seasonality':
                    $matched = ($rule->condition_value === $season);
                    break;

                case 'lead_time':
                    $targetDays = (int) $rule->condition_value;
                    $op = $rule->condition_operator ?: '<=';

                    $matched = match ($op) {
                        '<=' => ($leadTimeDays <= $targetDays),
                        '>=' => ($leadTimeDays >= $targetDays),
                        '<' => ($leadTimeDays < $targetDays),
                        '>' => ($leadTimeDays > $targetDays),
                        '==' => ($leadTimeDays === $targetDays),
                        default => ($leadTimeDays <= $targetDays),
                    };
                    break;
            }

            if ($matched) {
                // Calculate unit delta per person
                $amount = ($rule->adjustment_method === 'percentage')
                    ? ($basePrice * ((float) $rule->adjustment_value / 100.0))
                    : (float) $rule->adjustment_value;

                $delta = ($rule->adjustment_type === 'increase') ? $amount : -$amount;
                $rawTotalDelta += $delta;

                $appliedAdjustments[] = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'rule_type' => $rule->rule_type,
                    'condition_summary' => $rule->condition_summary,
                    'formatted_adjustment' => $rule->formatted_adjustment,
                    'adjustment_type' => $rule->adjustment_type,
                    'delta_per_pax' => round($delta, 2),
                    'total_delta' => round($delta * $paxCount, 2),
                ];
            }
        }

        // Apply clamping cap (+/- max cap % of base price)
        $cap = $this->getAdjustmentCap();
        $maxAdjustment = $basePrice * $cap;
        $minAdjustment = -$basePrice * $cap;

        $clampedDelta = max($minAdjustment, min($maxAdjustment, $rawTotalDelta));
        $wasClamped = ($clampedDelta !== $rawTotalDelta);

        $adjustedPricePerPax = max(500.00, round($basePrice + $clampedDelta, 2));

        $forecastData = $this->demandForecastService?->getForecastForDate($diveDate);

        return [
            'class_type' => $classType,
            'is_certified' => $isCertified,
            'dive_date' => Carbon::parse($diveDate)->format('Y-m-d'),
            'season' => $season,
            'season_label' => ucfirst(str_replace('_', '-', $season)) . ' Season',
            'demand' => $demand,
            'demand_label' => ucfirst($demand) . ' Demand',
            'forecast_source' => $forecastData ? 'ml_predictive' : 'historical_headcount',
            'predicted_participants' => $forecastData['predicted_participants'] ?? null,
            'lead_time_days' => $leadTimeDays,
            'base_price_per_pax' => $basePrice,
            'adjusted_price_per_pax' => $adjustedPricePerPax,
            'delta_per_pax' => round($clampedDelta, 2),
            'was_clamped' => $wasClamped,
            'adjustments' => $appliedAdjustments,
            'has_adjustments' => count($appliedAdjustments) > 0,
            'pax_count' => $paxCount,
            'subtotal' => round($adjustedPricePerPax * $paxCount, 2),
            'base_subtotal' => round($basePrice * $paxCount, 2),
            'total_savings_or_surcharge' => round($clampedDelta * $paxCount, 2),
        ];
    }
}
