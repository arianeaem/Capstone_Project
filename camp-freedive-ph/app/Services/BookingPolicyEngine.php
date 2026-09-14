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

        // Base policy tier calculation based on days until dive & force majeure
        if ($isForceMajeure) {
            $policyTier = 'force_majeure';
            $refundPercentage = 100;
            $calculatedRefund = (float) $booking->downpayment_amount;
            $rescheduleAllowed = true;
            $cancelAllowed = true;
            $rescheduleMessage = 'Storm/Typhoon Warning Active: Free reschedule granted due to marine safety advisory.';
            $cancelMessage = 'Storm/Typhoon Warning Active: 100% full refund available due to force majeure.';
        } elseif ($daysUntilDive > 14) {
            $policyTier = 'more_than_two_weeks';
            $refundPercentage = 100;
            $calculatedRefund = (float) $booking->downpayment_amount;
            $rescheduleAllowed = true;
            $cancelAllowed = true;
            $rescheduleMessage = 'Allowed: More than 14 days before dive date. Free reschedule to any available safe batch.';
            $cancelMessage = 'Eligible for 100% Full Downpayment Refund (₱' . number_format($booking->downpayment_amount, 2) . ') or Free Reschedule.';
        } elseif ($daysUntilDive >= 7 && $daysUntilDive <= 14) {
            $policyTier = 'within_two_weeks';
            $refundPercentage = 0;
            $calculatedRefund = 0.00;
            $rescheduleAllowed = true;
            $cancelAllowed = true;
            $rescheduleMessage = 'Allowed: Within 7 to 14 days window. Free reschedule to another available date.';
            $cancelMessage = '0% Refund (Downpayment Forfeited): Cancellations made within 7 to 14 days forfeit downpayment (free reschedule is permitted).';
        } else {
            // < 7 days
            $policyTier = 'within_one_week';
            $refundPercentage = 0;
            $calculatedRefund = 0.00;
            $rescheduleAllowed = false;
            $cancelAllowed = true;
            $rescheduleMessage = 'Not Allowed: Rescheduling closes 7 days before the dive date as coach, boat, and resort commitments are locked in.';
            $cancelMessage = 'Non-Refundable & Non-Reschedulable: Cancellations within 7 days forfeit downpayment unless an official Typhoon/Coast Guard Gale warning is active.';
        }

        // Status-specific overrides for self-service submission limits
        $hasPendingReschedule = ($booking->status === 'reschedule_requested');
        $hasPendingCancellation = ($booking->status === 'cancellation_requested');
        $isCancelled = in_array($booking->status, ['cancelled', 'cancelled_by_camp', 'cancelled_by_guest']);

        return [
            'days_until_dive' => $daysUntilDive,
            'is_force_majeure' => $isForceMajeure,
            'reschedule_allowed' => $isCancelled ? false : ($hasPendingReschedule ? false : $rescheduleAllowed),
            'reschedule_message' => $hasPendingReschedule 
                ? 'You already have a pending reschedule request under review by the camp.' 
                : ($isCancelled ? 'This booking is already cancelled.' : $rescheduleMessage),
            'cancel_allowed' => $isCancelled ? false : ($hasPendingCancellation ? false : $cancelAllowed),
            'cancel_message' => $hasPendingCancellation 
                ? 'A cancellation request is currently under review by the camp.' 
                : ($isCancelled ? 'This booking is already cancelled.' : $cancelMessage),
            'refund_percentage' => $refundPercentage,
            'calculated_refund' => $calculatedRefund,
            'policy_tier' => $policyTier,
            'has_pending_reschedule' => $hasPendingReschedule,
            'has_pending_cancellation' => $hasPendingCancellation,
            'is_cancelled' => $isCancelled,
        ];
    }
}
