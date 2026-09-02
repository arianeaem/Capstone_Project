<?php

namespace App\Http\Controllers;

use App\Mail\CancellationRequestedMail;
use App\Mail\RescheduleRequestedMail;
use App\Models\Booking;
use App\Models\CancellationRequest;
use App\Models\RescheduleRequest;
use App\Services\BookingPolicyEngine;
use App\Services\WeatherSafetyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ManageBookingController extends Controller
{
    public function __construct(
        protected BookingPolicyEngine $policyEngine,
        protected WeatherSafetyService $weatherService
    ) {}

    /**
     * Show the booking lookup form.
     */
    public function index(Request $request): View
    {
        $prefilledNumber = $request->query('number', '');
        $prefilledPin = $request->query('pin', '');

        return view('manage.lookup', compact('prefilledNumber', 'prefilledPin'));
    }

    /**
     * Look up a booking by number + PIN.
     */
    public function search(Request $request): RedirectResponse
    {
        $request->validate([
            'booking_number' => 'required|string',
            'pin' => 'required|string',
        ]);

        $booking = Booking::where('booking_number', strtoupper(trim($request->booking_number)))
            ->where('pin', trim($request->pin))
            ->first();

        if (!$booking) {
            return back()
                ->withInput()
                ->with('error', 'Booking not found - please check your details.');
        }

        session([
            'auth_booking_id' => $booking->id,
            'auth_booking_pin' => $booking->pin,
        ]);

        return redirect()->route('manage.show', [
            'booking_number' => $booking->booking_number,
            'pin' => $booking->pin,
        ]);
    }

    /**
     * Show the booking details and self-service management dashboard.
     */
    public function show(Request $request, string $booking_number): View|RedirectResponse
    {
        $pin = $request->query('pin', session('auth_booking_pin'));

        $booking = Booking::where('booking_number', strtoupper(trim($booking_number)))
            ->with(['participants', 'payments', 'rescheduleRequests' => fn($q) => $q->latest(), 'cancellationRequests' => fn($q) => $q->latest()])
            ->first();

        if (!$booking || ($booking->pin !== $pin && session('auth_booking_id') !== $booking->id)) {
            return redirect()->route('manage.index')
                ->with('error', 'Booking not found - please check your details.');
        }

        // Live evaluation of the policy engine
        $policy = $this->policyEngine->evaluate($booking);

        // Marine forecast for current booking date
        $currentForecast = $this->weatherService->getForecast($booking->start_date, $booking->end_date);

        return view('manage.detail', compact('booking', 'policy', 'currentForecast'));
    }

    /**
     * Submit a request to reschedule the dive date.
     */
    public function reschedule(Request $request, string $booking_number): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string',
            'requested_start_date' => 'required|date|after_or_equal:today',
            'requested_end_date' => 'required|date|after:requested_start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        $booking = Booking::where('booking_number', strtoupper(trim($booking_number)))
            ->where('pin', trim($validated['pin']))
            ->firstOrFail();

        if ($booking->status === 'pending_downpayment') {
            return back()->with('error', 'Your booking cannot be rescheduled because the required downpayment has not been paid.');
        }

        $policy = $this->policyEngine->evaluate($booking);
        if (!$policy['reschedule_allowed']) {
            return back()->with('error', 'Rescheduling is not allowed: ' . $policy['reschedule_message']);
        }

        // Check weather for requested date
        $forecast = $this->weatherService->getForecast($validated['requested_start_date'], $validated['requested_end_date']);
        if (!$forecast['is_bookable']) {
            return back()->with('error', 'The requested new date has a Critical Storm Warning. Please pick an alternative safe date.');
        }

        $rescheduleRequest = RescheduleRequest::create([
            'booking_id' => $booking->id,
            'current_start_date' => $booking->start_date,
            'current_end_date' => $booking->end_date,
            'requested_start_date' => $validated['requested_start_date'],
            'requested_end_date' => $validated['requested_end_date'],
            'reason' => $validated['reason'] ?? 'Customer requested reschedule',
            'status' => 'pending',
        ]);

        $booking->update([
            'status' => 'reschedule_requested',
        ]);

        try {
            Mail::to($booking->contact_email)->send(new RescheduleRequestedMail($booking, $rescheduleRequest));
        } catch (\Exception $e) {
            Log::warning('Reschedule email failed: ' . $e->getMessage());
        }

        return redirect()->route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin])
            ->with('success', "Your reschedule request has been sent to the camp for approval. You'll be notified once it's confirmed.");
    }

    /**
     * Submit a request to cancel the booking.
     */
    public function cancel(Request $request, string $booking_number): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string',
            'confirm_cancel_ack' => 'required|accepted',
            'reason' => 'nullable|string|max:500',
        ]);

        $booking = Booking::where('booking_number', strtoupper(trim($booking_number)))
            ->where('pin', trim($validated['pin']))
            ->firstOrFail();

        if ($booking->status === 'pending_downpayment') {
            return back()->with('error', 'Your booking cannot be cancelled because the required downpayment has not been paid.');
        }

        $policy = $this->policyEngine->evaluate($booking);
        if ($booking->status === 'cancelled' || $booking->status === 'cancellation_requested') {
            return back()->with('error', 'A cancellation is already processed or pending review.');
        }

        $cancellationRequest = CancellationRequest::create([
            'booking_id' => $booking->id,
            'calculated_refund_amount' => $policy['calculated_refund'],
            'reason' => $validated['reason'] ?? 'Customer requested cancellation',
            'force_majeure_flag' => $policy['is_force_majeure'],
            'status' => 'pending',
        ]);

        $booking->update([
            'status' => 'cancellation_requested',
        ]);

        try {
            Mail::to($booking->contact_email)->send(new CancellationRequestedMail($booking, $cancellationRequest));
        } catch (\Exception $e) {
            Log::warning('Cancellation email failed: ' . $e->getMessage());
        }

        return redirect()->route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin])
            ->with('success', "Your cancellation request has been submitted for camp review. You'll be notified once processed.");
    }
}
