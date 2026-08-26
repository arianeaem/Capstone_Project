<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\BookingStatusLog;
use App\Models\Payment;
use App\Models\PaymentStatusLog;
use App\Services\AuditLogger;
use App\Services\WeatherSafetyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected WeatherSafetyService $weatherSafetyService
    ) {}

    /**
     * Show the multi-step public customer booking wizard.
     */
    public function create(Request $request): View
    {
        $selectedClass = strtolower($request->query('class', 'discovery'));
        if (!in_array($selectedClass, ['discovery', 'fundive', 'refinement'])) {
            $selectedClass = 'discovery';
        }

        $pickupPoints = [
            [
                'id' => 'monumento',
                'name' => 'Monumento Hypermarket - 2:30 AM',
                'time' => '2:30 AM',
                'address' => 'Monumento Hypermarket, Caloocan City',
            ],
            [
                'id' => 'tiendesitas',
                'name' => 'Shell Tiendesitas - 3:00 AM',
                'time' => '3:00 AM',
                'address' => 'Shell C5 Tiendesitas, Pasig City',
            ],
            [
                'id' => 'market_market',
                'name' => 'Market Market Taxi Bay - 3:40 AM',
                'time' => '3:40 AM',
                'address' => 'Market! Market! Taxi Bay, BGC, Taguig City',
            ],
            [
                'id' => 'alabang',
                'name' => 'Alabang Starmall - 4:15 AM',
                'time' => '4:15 AM',
                'address' => 'Starmall Alabang Southbound, Muntinlupa City',
            ],
            [
                'id' => 'sto_tomas',
                'name' => 'Sto Tomas Exit - 5:30 AM',
                'time' => '5:30 AM',
                'address' => 'Sto. Tomas SLEX / STAR Tollway Exit, Batangas',
            ],
        ];

        return view('booking.wizard', compact('selectedClass', 'pickupPoints'));
    }

    /**
     * Check weather / marine safety forecast for selected dates.
     */
    public function checkWeather(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $forecast = $this->weatherSafetyService->getForecast(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($forecast);
    }

    /**
     * Process and store a completed customer reservation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_type' => 'required|string|in:discovery,fundive,refinement',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'participants' => 'required|array|min:1|max:10',
            'participants.*.name' => 'required|string|max:255',
            'participants.*.age' => 'required|integer|min:10|max:80',
            'participants.*.health_condition' => 'nullable|string|max:500',
            'participants.*.swimmer_status' => 'nullable|string|in:non_swimmer,beginner,intermediate,advanced,swimmer',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'contact_facebook' => 'nullable|string|max:255',
            'pickup_option' => 'required|string|in:carpool,own',
            'pickup_location' => 'nullable|string|max:255',
            'boat_dive' => 'nullable|boolean',
            'confirmation_ack' => 'nullable|boolean',
            'payment_method' => 'required|string|in:gcash,bpi,dob,bpi_bank_transfer,paymongo,card,qrph,paymaya,grab_pay,paymongo_gcash,paymongo_card',
        ], [
            'contact_phone.regex' => 'Please enter a valid Philippine mobile number (e.g. 09171234567).',
        ]);

        $paxCount = count($validated['participants']);
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $endDate = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // Check 45-pax Capacity per weekend date
        $existingPaxOnDate = BookingParticipant::whereHas('booking', function ($q) use ($startDate) {
            $q->whereDate('start_date', $startDate)
              ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled']);
        })->count();

        if (($existingPaxOnDate + $paxCount) > 45) {
            $slotsLeft = max(0, 45 - $existingPaxOnDate);
            return response()->json([
                'success' => false,
                'message' => "Capacity limit reached for {$startDate}. Only {$slotsLeft} slot(s) remaining for this weekend trip. Please select another date or reduce group size.",
            ], 422);
        }

        // Pricing Configuration
        $classPrice = match ($validated['class_type']) {
            'discovery' => 4250.00,
            'fundive' => 3800.00,
            'refinement' => 4500.00,
            default => 4250.00,
        };

        $lguFee = 300.00 * $paxCount;
        $envFee = 50.00 * $paxCount;
        $carpoolFee = ($validated['pickup_option'] === 'carpool') ? (1000.00 * $paxCount) : 0.00;
        $boatDiveFee = (!empty($validated['boat_dive']) && $validated['boat_dive']) ? (800.00 * $paxCount) : 0.00;

        $subtotal = $classPrice * $paxCount;
        $totalAmount = $subtotal + $lguFee + $envFee + $carpoolFee + $boatDiveFee;
        
        // Standard downpayment is ₱3,000 per head (or full amount if total < downpayment)
        $downpaymentAmount = min($totalAmount, 3000.00 * $paxCount);
        $balanceAmount = max(0, $totalAmount - $downpaymentAmount);

        // Find or associate existing batch for this date if exists
        $existingBatch = Batch::whereDate('start_date', $startDate)->first();

        // Generate Unique Booking Number and 4-Digit Security PIN
        $bookingNumber = 'CFP-' . date('Y') . '-' . strtoupper(Str::random(5));
        $pin = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'pin' => $pin,
                'batch_id' => $existingBatch ? $existingBatch->id : null,
                'class_type' => $validated['class_type'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'pickup_option' => $validated['pickup_option'],
                'pickup_location' => $validated['pickup_location'] ?? null,
                'carpool_fee' => $carpoolFee,
                'boat_dive' => !empty($validated['boat_dive']) && $validated['boat_dive'],
                'boat_dive_fee' => $boatDiveFee,
                'lgu_fee' => $lguFee,
                'environmental_fee' => $envFee,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'downpayment_amount' => $downpaymentAmount,
                'balance_amount' => $balanceAmount,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'contact_facebook' => $validated['contact_facebook'] ?? null,
                'status' => 'confirmed',
            ]);

            // Save Participants
            foreach ($validated['participants'] as $pData) {
                $booking->participants()->create([
                    'name' => $pData['name'],
                    'age' => (int) $pData['age'],
                    'health_condition' => $pData['health_condition'] ?? 'None',
                    'swimmer_status' => $pData['swimmer_status'] ?? 'beginner',
                    'price_per_person' => $classPrice,
                ]);
            }

            // Create Payment Record
            $paymentMethod = $validated['payment_method'];
            $transactionId = 'PAY-' . strtoupper(Str::random(10));

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'amount' => $downpaymentAmount,
                'fee_amount' => 0.00,
                'net_amount' => $downpaymentAmount,
                'payment_type' => 'downpayment',
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Booking Status Log
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'pending',
                'new_status' => 'confirmed',
                'changed_by' => null,
                'note' => "Online reservation completed. Downpayment of ₱" . number_format($downpaymentAmount, 2) . " verified via {$paymentMethod}.",
                'created_at' => now(),
            ]);

            // Payment Status Log
            PaymentStatusLog::create([
                'payment_id' => $payment->id,
                'old_status' => 'pending',
                'new_status' => 'paid',
                'changed_by' => null,
                'note' => "Downpayment of ₱" . number_format($downpaymentAmount, 2) . " captured via {$paymentMethod}.",
                'created_at' => now(),
            ]);

            AuditLogger::log(
                'BOOKING_CREATED',
                "New booking #{$booking->booking_number} created for {$booking->contact_name} ({$paxCount} pax, {$booking->class_type})",
                null,
                $booking->contact_name,
                $request
            );

            DB::commit();

            // Send Confirmation Email to Guest
            try {
                \Illuminate\Support\Facades\Mail::to($booking->contact_email)->send(
                    new \App\Mail\BookingConfirmedMail($booking->fresh()->load('participants', 'payments'))
                );
            } catch (\Throwable $mailEx) {
                \Illuminate\Support\Facades\Log::warning("Booking confirmation email could not be sent to {$booking->contact_email}: " . $mailEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'booking_number' => $booking->booking_number,
                'pin' => $booking->pin,
                'booking_id' => $booking->id,
                'downpayment_paid' => $downpaymentAmount,
                'balance_due' => $balanceAmount,
                'manage_url' => route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin]),
                'redirect_url' => route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin]),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving your reservation: ' . $e->getMessage(),
            ], 500);
        }
    }
}
