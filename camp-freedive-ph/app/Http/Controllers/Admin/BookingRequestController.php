<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\CancellationRequest;
use App\Models\RescheduleRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingRequestController extends Controller
{
    /**
     * Display the dedicated Pending Requests queue.
     */
    public function index(Request $request): View
    {
        $pendingReschedules = RescheduleRequest::with('booking.participants')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $pendingCancellations = CancellationRequest::with('booking.participants')
            ->where('status', 'pending')
            ->latest()
            ->get();

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

            // Update booking dates and status
            $booking->update([
                'start_date' => $rescheduleRequest->requested_start_date,
                'end_date' => $rescheduleRequest->requested_end_date,
                'status' => 'rescheduled',
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
                'new_status' => 'rescheduled',
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

        return back()->with('success', "Reschedule request for Booking #{$booking->booking_number} has been approved!");
    }

    /**
     * Reject customer reschedule request.
     */
    public function rejectReschedule(Request $request, RescheduleRequest $rescheduleRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $rescheduleRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Please provide an explanation for rejecting this reschedule request.',
        ]);

        DB::transaction(function () use ($rescheduleRequest, $booking, $validated, $currentUser) {
            // Restore booking status to confirmed
            $booking->update(['status' => 'confirmed']);

            $rescheduleRequest->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'reschedule_requested',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => "Reschedule request rejected by {$currentUser->name} - Reason: {$validated['admin_notes']}",
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'RESCHEDULE_REJECTED',
            "Reschedule request rejected for Booking #{$booking->booking_number} by {$currentUser->name}. Reason: {$validated['admin_notes']}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('info', "Reschedule request for Booking #{$booking->booking_number} was rejected.");
    }

    /**
     * Approve customer cancellation request.
     */
    public function approveCancellation(Request $request, CancellationRequest $cancellationRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $cancellationRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($cancellationRequest, $booking, $validated, $currentUser) {
            $booking->update(['status' => 'cancelled_by_guest']);

            $cancellationRequest->update([
                'status' => 'approved',
                'admin_notes' => $validated['admin_notes'] ?? 'Cancellation approved. Calculated refund queued for processing.',
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'cancellation_requested',
                'new_status' => 'cancelled_by_guest',
                'changed_by' => $currentUser->id,
                'note' => "Cancellation approved by {$currentUser->name} (Refund due: ₱" . number_format($cancellationRequest->calculated_refund_amount, 2) . ")" . ($validated['admin_notes'] ? " - {$validated['admin_notes']}" : ''),
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'CANCELLATION_APPROVED',
            "Cancellation request approved for Booking #{$booking->booking_number} by {$currentUser->name}. Refund amount: ₱{$cancellationRequest->calculated_refund_amount}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Cancellation for Booking #{$booking->booking_number} approved. Please process the refund of ₱" . number_format($cancellationRequest->calculated_refund_amount, 2) . " in the Payments & Refunds module.");
    }

    /**
     * Reject customer cancellation request.
     */
    public function rejectCancellation(Request $request, CancellationRequest $cancellationRequest): RedirectResponse
    {
        $currentUser = Auth::user();
        $booking = $cancellationRequest->booking;

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ], [
            'admin_notes.required' => 'Please provide an explanation for rejecting this cancellation request.',
        ]);

        DB::transaction(function () use ($cancellationRequest, $booking, $validated, $currentUser) {
            $booking->update(['status' => 'confirmed']);

            $cancellationRequest->update([
                'status' => 'rejected',
                'admin_notes' => $validated['admin_notes'],
                'reviewed_by' => $currentUser->id,
                'reviewed_at' => now(),
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'cancellation_requested',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => "Cancellation rejected by {$currentUser->name} - Reason: {$validated['admin_notes']}",
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'CANCELLATION_REJECTED',
            "Cancellation request rejected for Booking #{$booking->booking_number} by {$currentUser->name}. Reason: {$validated['admin_notes']}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('info', "Cancellation request for Booking #{$booking->booking_number} was rejected.");
    }
}
