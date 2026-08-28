<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\CancellationRequest;
use App\Models\RefundRequest;
use App\Models\RescheduleRequest;
use App\Services\AuditLogger;
use App\Services\BookingPolicyEngine;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingRequestController extends Controller
{
    public function __construct(
        protected BookingPolicyEngine $policyEngine
    ) {}

    /**
     * Display the dedicated Pending Requests queue.
     */
    public function index(Request $request): View
    {
        $pendingReschedules = RescheduleRequest::with(['booking.participants', 'booking.payments'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $pendingCancellations = CancellationRequest::with(['booking.participants', 'booking.payments'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $cancellationPolicies = [];
        foreach ($pendingCancellations as $req) {
            $cancellationPolicies[$req->id] = $this->policyEngine->evaluate($req->booking);
        }

        $reschedulePolicies = [];
        foreach ($pendingReschedules as $req) {
            $reschedulePolicies[$req->id] = $this->policyEngine->evaluate($req->booking);
        }

        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));

        $processedReschedules = RescheduleRequest::with(['booking', 'reviewer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->paginate($perPage, ['*'], 'reschedule_page')
            ->withQueryString();

        $processedCancellations = CancellationRequest::with(['booking', 'reviewer'])
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('reviewed_at')
            ->paginate($perPage, ['*'], 'cancellation_page')
            ->withQueryString();

        return view('admin.bookings.requests', compact(
            'pendingReschedules',
            'pendingCancellations',
            'cancellationPolicies',
            'reschedulePolicies',
            'processedReschedules',
            'processedCancellations'
        ));
    }

    /**
     * Approve customer reschedule request.
     */
    public function approveReschedule(Request $request, RescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $rescheduleRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($rescheduleRequest, $booking, $validated, $currentUser) {
            $oldDates = "{$booking->start_date->format('M d, Y')} - {$booking->end_date->format('M d, Y')}";
            $newDates = "{$rescheduleRequest->requested_start_date->format('M d, Y')} - {$rescheduleRequest->requested_end_date->format('M d, Y')}";

            // Update booking dates and detach from existing batch so it can be assigned to new date
            $booking->update([
                'batch_id' => null,
                'start_date' => $rescheduleRequest->requested_start_date,
                'end_date' => $rescheduleRequest->requested_end_date,
                'status' => 'confirmed',
            ]);

            // Mark request approved
            $rescheduleRequest->update([
                'status' => 'approved',
                'admin_notes' => $validated['admin_notes'] ?? 'Reschedule request approved by camp staff.',
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            // Status Log
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'reschedule_requested',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => "Reschedule approved ({$oldDates} → {$newDates})" . ($validated['admin_notes'] ? " - {$validated['admin_notes']}" : ''),
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'RESCHEDULE_APPROVED',
            "Reschedule request approved for Booking #{$booking->booking_number} by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Reschedule request for Booking #{$booking->booking_number} has been approved! Booking moved to {$rescheduleRequest->requested_start_date->format('M d, Y')}.");
    }

    /**
     * Reject customer reschedule request.
     */
    public function rejectReschedule(Request $request, RescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $rescheduleRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $reason = $validated['admin_notes'] ?? 'Reschedule request rejected by camp administration.';

        DB::transaction(function () use ($rescheduleRequest, $booking, $reason, $currentUser) {
            // Restore booking status to confirmed
            $booking->update(['status' => 'confirmed']);

            $rescheduleRequest->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'reschedule_requested',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => "Reschedule request rejected by {$currentUser->name} - Reason: {$reason}",
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'RESCHEDULE_REJECTED',
            "Reschedule request rejected for Booking #{$booking->booking_number} by {$currentUser->name}. Reason: {$reason}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('info', "Reschedule request for Booking #{$booking->booking_number} was rejected.");
    }

    /**
     * Approve customer cancellation request with policy choices.
     */
    public function approveCancellation(Request $request, CancellationRequest $cancellationRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $cancellationRequest->booking;

        $validated = $request->validate([
            'action_type' => 'nullable|string|in:policy_refund,full_refund,forfeit',
            'refund_amount' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $policy = $this->policyEngine->evaluate($booking);
        $actionType = $validated['action_type'] ?? 'policy_refund';

        // Determine refund amount & forfeit status
        if ($actionType === 'forfeit') {
            $refundAmount = 0.00;
            $refundPercentage = 0;
            $isForfeited = true;
        } elseif ($actionType === 'full_refund') {
            $refundAmount = (float) $booking->paid_amount;
            $refundPercentage = 100;
            $isForfeited = false;
        } else { // policy_refund
            $refundAmount = isset($validated['refund_amount']) ? (float) $validated['refund_amount'] : (float) ($policy['calculated_refund'] ?? $cancellationRequest->calculated_refund_amount);
            $refundPercentage = $policy['refund_percentage'] ?? 0;
            $isForfeited = ($refundAmount <= 0);
        }

        DB::transaction(function () use ($cancellationRequest, $booking, $validated, $currentUser, $policy, $refundAmount, $refundPercentage, $isForfeited) {
            $booking->update([
                'status' => 'cancelled_by_guest',
                'batch_id' => null,
            ]);

            $cancellationRequest->update([
                'status' => 'approved',
                'calculated_refund_amount' => $refundAmount,
                'admin_notes' => $validated['admin_notes'] ?? ($isForfeited ? 'Cancellation approved (Downpayment forfeited per policy).' : 'Cancellation approved. Refund queued for processing.'),
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            // Create RefundRequest records for completed payments
            $completedPayments = $booking->payments()->where('status', 'completed')->get();
            foreach ($completedPayments as $payment) {
                RefundRequest::create([
                    'payment_id' => $payment->id,
                    'booking_id' => $booking->id,
                    'requested_by' => 'guest_cancellation',
                    'requested_at' => now(),
                    'eligibility_calculated' => [
                        'days_until_dive' => max(0, Carbon::now()->diffInDays($booking->start_date, false)),
                        'eligible_for_refund' => !$isForfeited && ($refundAmount > 0),
                        'refund_percentage' => $refundPercentage,
                        'window_label' => $policy['policy_tier'] ?? 'Standard Policy',
                        'policy_action_text' => $isForfeited ? 'Cancellation within forfeiture window.' : "Approved refund of ₱" . number_format($refundAmount, 2),
                    ],
                    'status' => $isForfeited ? 'forfeited' : 'pending',
                    'forfeit_reason' => $isForfeited ? 'cancellation_outside_policy_window' : null,
                    'notes' => "Approved from Guest Cancellation Request. " . ($validated['admin_notes'] ?? ''),
                ]);
            }

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'cancellation_requested',
                'new_status' => 'cancelled_by_guest',
                'changed_by' => $currentUser->id,
                'note' => "Cancellation approved by {$currentUser->name} (" . ($isForfeited ? "Forfeited" : "Refund due: ₱" . number_format($refundAmount, 2)) . ")" . ($validated['admin_notes'] ? " - {$validated['admin_notes']}" : ''),
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'CANCELLATION_APPROVED',
            "Cancellation request approved for Booking #{$booking->booking_number} by {$currentUser->name}. Refund amount: ₱{$refundAmount}",
            $currentUser,
            $currentUser->name,
            $request
        );

        if (!$isForfeited && $refundAmount > 0) {
            return redirect()->route('admin.payments.refunds')->with('success', "Cancellation for Booking #{$booking->booking_number} approved! Refund of ₱" . number_format($refundAmount, 2) . " is now queued below in Pending Refunds.");
        }

        return back()->with('success', "Cancellation for Booking #{$booking->booking_number} has been approved (Downpayment forfeited per policy).");
    }

    /**
     * Reject customer cancellation request.
     */
    public function rejectCancellation(Request $request, CancellationRequest $cancellationRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $cancellationRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $reason = $validated['admin_notes'] ?? 'Cancellation request rejected by camp administration.';

        DB::transaction(function () use ($cancellationRequest, $booking, $reason, $currentUser) {
            $booking->update(['status' => 'confirmed']);

            $cancellationRequest->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'cancellation_requested',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => "Cancellation rejected by {$currentUser->name} - Reason: {$reason}",
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'CANCELLATION_REJECTED',
            "Cancellation request rejected for Booking #{$booking->booking_number} by {$currentUser->name}. Reason: {$reason}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('info', "Cancellation request for Booking #{$booking->booking_number} was rejected.");
    }
}
