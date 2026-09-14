<?php

namespace App\Services;

use App\Models\BookingParticipant;
use App\Models\PricingRule;
use Carbon\Carbon;

/**
 * Dynamic Yield Management & Pricing Rule Engine.
 *
 * Business Model & Economic Rationale:
 * 1. Multi-Factor Dynamic Pricing: Evaluates seasonal trends, occupancy velocity, and booking lead times
 *    to optimize freediving camp capacity utilization across the year.
 * 2. Strict ±30% Price Clamping Cap (ADJUSTMENT_PERCENTAGE_CAP):
 *    Protects customer trust and transparent pricing by strictly bounding cumulative discounts/surcharges
 *    between -30% and +30% of the base class tier price.
 * 3. Batangas Micro-Climate Seasonality:
 *    - Peak (Nov - Apr): Amihan northeast monsoon delivers dry, calm conditions and peak tourism demand.
 *    - Shoulder (May, Oct): Monsoon transitional months with moderate occupancy.
 *    - Off-Peak (Jun - Sep): Habagat southwest monsoon brings wet weather; discounts incentivize advance bookings.
 */
class PricingRuleEngine
{
    // TODO: Connect pricing rule evaluation with Prophet/XGBoost seasonal demand forecasts to dynamically adjust lead-time pricing tiers.

    /**
     * Percentage cap to clamp stacked adjustments (+/- 30% of base price).
     */
    public const ADJUSTMENT_PERCENTAGE_CAP = 0.30;

    /**
     * Standard Base Prices per class type.
     */
    public const BASE_PRICES = [
        'discovery' => 4250.00,
        'fundive_certified' => 2500.00,
        'fundive_non_certified' => 3300.00,
        'refinement' => 4100.00,
    ];

    /**
     * Resolve base price for given class and diver certification.
     */
    public function getBasePrice(string $classType, bool $isCertified = false): float
    {
        $classKey = strtolower($classType);
        if ($classKey === 'fundive') {
            return $isCertified ? self::BASE_PRICES['fundive_certified'] : self::BASE_PRICES['fundive_non_certified'];
        }
        return self::BASE_PRICES[$classKey] ?? self::BASE_PRICES['discovery'];
    }

    /**
     * Determine calendar season for a date in Batangas:
     * - Peak: November to April (Months 11, 12, 1, 2, 3, 4)
     * - Shoulder: May and October (Months 5, 10)
     * - Off-Peak: June to September (Months 6, 7, 8, 9)
     */
    public function getSeasonForDate(string|Carbon $date): string
    {
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
     * Calculate live demand level for a date based on booking occupancy:
     * - High: > 60% capacity (> 27 pax of 45)
     * - Medium: 25% - 60% capacity (11 to 27 pax)
     * - Low: < 25% capacity (< 11 pax)
     */
    public function getDemandForDate(string|Carbon $date): string
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');

        $bookedCount = BookingParticipant::whereHas('booking', function ($q) use ($dateStr) {
            $q->whereDate('start_date', $dateStr)
              ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment']);
        })->count();

        $occupancyRate = $bookedCount / 45.0;

        if ($occupancyRate >= 0.60) {
            return 'high';
        }

        if ($occupancyRate >= 0.25) {
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

        // Apply clamping cap (+/- 30% of base price)
        $maxAdjustment = $basePrice * self::ADJUSTMENT_PERCENTAGE_CAP;
        $minAdjustment = -$basePrice * self::ADJUSTMENT_PERCENTAGE_CAP;

        $clampedDelta = max($minAdjustment, min($maxAdjustment, $rawTotalDelta));
        $wasClamped = ($clampedDelta !== $rawTotalDelta);

        $adjustedPricePerPax = max(500.00, round($basePrice + $clampedDelta, 2));

        return [
            'class_type' => $classType,
            'is_certified' => $isCertified,
            'dive_date' => Carbon::parse($diveDate)->format('Y-m-d'),
            'season' => $season,
            'season_label' => ucfirst(str_replace('_', '-', $season)) . ' Season',
            'demand' => $demand,
            'demand_label' => ucfirst($demand) . ' Demand',
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
