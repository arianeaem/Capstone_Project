<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class BookingPolicyEngine
{
    public function __construct(
        protected WeatherSafetyService $weatherService
    ) {}

    /**
     * Evaluate the live reschedule and cancellation policy for a booking.
     */
    public function evaluate(Booking $booking): array
    {
        $now = Carbon::now()->startOfDay();
        $diveDate = Carbon::parse($booking->start_date)->startOfDay();
        $daysUntilDive = (int) $now->diffInDays($diveDate, false);

        // Check if there is an active storm / typhoon warning for that dive date
        $isForceMajeure = $this->weatherService->isStormSignalActive($booking->start_date);

        // Policy evaluations
        if ($booking->status === 'cancelled') {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => $isForceMajeure,
                'reschedule_allowed' => false,
                'reschedule_message' => 'This booking is already cancelled.',
                'cancel_allowed' => false,
                'cancel_message' => 'This booking is already cancelled.',
                'refund_percentage' => 0,
                'calculated_refund' => 0.00,
                'policy_tier' => 'cancelled',
            ];
        }

        if ($booking->status === 'reschedule_requested') {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => $isForceMajeure,
                'reschedule_allowed' => false,
                'reschedule_message' => 'You already have a pending reschedule request under review by the camp.',
                'cancel_allowed' => false,
                'cancel_message' => 'Please wait for your pending reschedule request to be processed before requesting cancellation.',
                'refund_percentage' => 0,
                'calculated_refund' => 0.00,
                'policy_tier' => 'pending_reschedule',
            ];
        }

        if ($booking->status === 'cancellation_requested') {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => $isForceMajeure,
                'reschedule_allowed' => false,
                'reschedule_message' => 'A cancellation request is already pending approval for this booking.',
                'cancel_allowed' => false,
                'cancel_message' => 'Your cancellation request is currently under review by the camp.',
                'refund_percentage' => 0,
                'calculated_refund' => 0.00,
                'policy_tier' => 'pending_cancellation',
            ];
        }

        // Dive date in past
        if ($daysUntilDive < 0) {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => false,
                'reschedule_allowed' => false,
                'reschedule_message' => 'This dive trip has already taken place.',
                'cancel_allowed' => false,
                'cancel_message' => 'Past bookings cannot be cancelled or refunded.',
                'refund_percentage' => 0,
                'calculated_refund' => 0.00,
                'policy_tier' => 'past',
            ];
        }

        // Force Majeure (Typhoon / Gale warning override)
        if ($isForceMajeure) {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => true,
                'reschedule_allowed' => true,
                'reschedule_message' => 'Storm/Typhoon Warning Active: Free reschedule granted due to marine safety advisory.',
                'cancel_allowed' => true,
                'cancel_message' => 'Storm/Typhoon Warning Active: 100% full refund available due to force majeure.',
                'refund_percentage' => 100,
                'calculated_refund' => (float) $booking->downpayment_amount,
                'policy_tier' => 'force_majeure',
            ];
        }

        // Tier 1: More than 2 weeks (> 14 days)
        if ($daysUntilDive > 14) {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => false,
                'reschedule_allowed' => true,
                'reschedule_message' => 'Allowed: More than 14 days before dive date. You may reschedule to any available safe batch.',
                'cancel_allowed' => true,
                'cancel_message' => 'Eligible for 100% Full Downpayment Refund (₱' . number_format($booking->downpayment_amount, 2) . ').',
                'refund_percentage' => 100,
                'calculated_refund' => (float) $booking->downpayment_amount,
                'policy_tier' => 'more_than_two_weeks',
            ];
        }

        // Tier 2: Within 2 weeks (7 to 14 days)
        if ($daysUntilDive >= 7 && $daysUntilDive <= 14) {
            return [
                'days_until_dive' => $daysUntilDive,
                'is_force_majeure' => false,
                'reschedule_allowed' => true,
                'reschedule_message' => 'Allowed: Within 7–14 days window. You may move your slot to another future date once.',
                'cancel_allowed' => false,
                'cancel_message' => 'Not Eligible for Refund: Under camp policy, cancellations made within 14 days of the dive date forfeit the downpayment (rescheduling is permitted).',
                'refund_percentage' => 0,
                'calculated_refund' => 0.00,
                'policy_tier' => 'within_two_weeks',
            ];
        }

        // Tier 3: Within 1 week (< 7 days)
        return [
            'days_until_dive' => $daysUntilDive,
            'is_force_majeure' => false,
            'reschedule_allowed' => false,
            'reschedule_message' => 'Not Allowed: Rescheduling closes 7 days before the dive date as coach, boat, and lodging commitments are locked in.',
            'cancel_allowed' => false,
            'cancel_message' => 'Not Eligible for Refund: Cancellations within 7 days forfeit downpayment unless a formal Typhoon/Coast Guard Gale warning is issued.',
            'refund_percentage' => 0,
            'calculated_refund' => 0.00,
            'policy_tier' => 'within_one_week',
        ];
    }
}
