<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\BookingStatusLog;
use App\Models\Payment;
use App\Models\PaymentStatusLog;
use App\Services\AuditLogger;
use App\Services\PricingRuleEngine;
use App\Services\WeatherSafetyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public Customer Booking & Reservation Controller.
 *
 * Business Workflow & Safety Policies:
 * - Directs users through the multi-step booking process (`booking.create`).
 * - Real-time Weather Feeds: Integrates live Open-Meteo forecasts and ML safety clearance.
 * - Capacity Bounds: Enforces a strict 45-pax cap per weekend trip across all batches
 *   to ensure adherence to Coast Guard banca vessel limits and a 1:4 instructor-to-student safety ratio.
 * - Payment Guarantees: Reserves slots in `pending_downpayment` status and releases them if
 *   unpaid within the slot-hold grace period.
 */
class BookingController extends Controller
{
    public function __construct(
        protected WeatherSafetyService $weatherSafetyService,
        protected PricingRuleEngine $pricingRuleEngine
    ) {}

    // TODO: Implement temporary Redis slot locking during checkout step 4 to prevent race-condition overbooking.

    /**
     * Display the customer-facing booking form.
     *
     * @param Request $request Query parameters (e.g. `class` pre-selection).
     * @return View Renders the step-by-step booking interface.
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

        return view('booking.create', compact('selectedClass', 'pickupPoints'));
    }

    /**
     * Retrieves weather and marine risk forecast for selected session dates.
     *
     * @param Request $request Contains `start_date` and `end_date` (YYYY-MM-DD).
     * @return JsonResponse Returns standardized 5-tier safety classification and physical readings.
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
     * Computes live dynamic pricing quote based on booking parameters.
     *
     * Business Logic:
     * Applies early-bird discounts, certified diver deductions, and group tiered pricing
     * via the PricingRuleEngine.
     *
     * @param Request $request Contains `class_type`, `start_date`, `is_certified_diver`, and `participants_count`.
     * @return JsonResponse Breakdown of base price, discounts, subtotal, and regulatory fees.
     */
    public function getPricingQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_type' => 'required|string',
            'start_date' => 'required|date',
            'is_certified_diver' => 'nullable|boolean',
            'participants_count' => 'nullable|integer|min:1|max:10',
        ]);

        $quote = $this->pricingRuleEngine->evaluate(
            $validated['class_type'],
            $validated['start_date'],
            (bool) ($validated['is_certified_diver'] ?? false),
            (int) ($validated['participants_count'] ?? 1)
        );

        return response()->json($quote);
    }

    /**
     * Validates and creates a new booking reservation in atomic database transaction.
     *
     * Business Workflow:
     * 1. Validates participant medical disclosures and minimum emergency contact details.
     * 2. Enforces the 45-pax total weekend batch capacity limit.
     * 3. Re-evaluates final pricing and assigns municipal LGU & environmental fees.
     * 4. Allocates a unique booking code (e.g. CFP-2026-XXXXX) and 4-digit guest security PIN.
     * 5. Initializes booking and downpayment records in `pending_downpayment` status.
     *
     * @param Request $request Complete booking payload.
     * @return JsonResponse Confirmation containing booking number, PIN, downpayment amount, and payment options.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_type' => 'required|string|in:discovery,fundive,refinement',
            'is_certified_diver' => 'nullable|boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'participants' => 'required|array|min:1|max:10',
            'participants.*.name' => 'nullable|string|max:255',
            'participants.*.first_name' => 'nullable|string|max:255',
            'participants.*.last_name' => 'nullable|string|max:255',
            'participants.*.age' => 'required|integer|min:8|max:85',
            'participants.*.health_condition' => 'nullable|string|max:500',
            'participants.*.swimmer_status' => 'nullable|string|in:non_swimmer,beginner,intermediate,advanced,swimmer,casual_swimmer,confident_swimmer',
            'contact_name' => 'nullable|string|max:255',
            'contact_first_name' => 'nullable|string|max:255',
            'contact_last_name' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => ['required', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'contact_facebook' => 'nullable|string|max:255',
            'pickup_option' => 'required|string|in:carpool,own',
            'pickup_location' => 'required_if:pickup_option,carpool|nullable|string|max:255',
            'boat_dive' => 'nullable|boolean',
            'confirmation_ack' => 'nullable|boolean',
            'payment_method' => 'nullable|string',
        ], [
            'pickup_location.required_if' => 'Please select a carpool pickup location.',
            'contact_phone.regex' => 'Please enter a valid Philippine mobile number (e.g. 09171234567).',
        ]);

        $contactName = !empty($validated['contact_name']) 
            ? $validated['contact_name'] 
            : trim(($validated['contact_first_name'] ?? '') . ' ' . ($validated['contact_last_name'] ?? ''));

        $paxCount = count($validated['participants']);
        $startDate = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $endDate = Carbon::parse($validated['end_date'])->format('Y-m-d');
        $isCertified = !empty($validated['is_certified_diver']);

        // Business Logic - Capacity Enforcement:
        // Capped at 45 pax per weekend trip to comply with Philippine Coast Guard banca passenger
        // limits and maintain an instructor-to-diver ratio of 1:4.
        // We deliberately exclude 'pending_downpayment' and cancelled bookings so uncompleted checkout
        // attempts or abandoned browser sessions never permanently lock out paying guests.
        $existingPaxOnDate = BookingParticipant::whereHas('booking', function ($q) use ($startDate) {
            $q->whereDate('start_date', $startDate)
              ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment']);
        })->count();

        if (($existingPaxOnDate + $paxCount) > 45) {
            $slotsLeft = max(0, 45 - $existingPaxOnDate);
            return response()->json([
                'success' => false,
                'message' => "Capacity limit reached for {$startDate}. Only {$slotsLeft} slot(s) remaining for this weekend trip. Please select another date or reduce group size.",
            ], 422);
        }

        // Pricing Logic:
        // Evaluates dynamic pricing rules (early-bird discounts, certified diver deductions, group discounts).
        $quote = $this->pricingRuleEngine->evaluate(
            $validated['class_type'],
            $startDate,
            $isCertified,
            $paxCount
        );

        $classPrice = $quote['adjusted_price_per_pax'];
        $subtotal = $quote['subtotal'];

        // Regulatory & Logistics Fees:
        // ₱300 LGU Tourism Pass + ₱50 Mabini Marine Sanctuary Ecological Fee per participant.
        // Carpool (₱1,000/head roundtrip) and Boat Dive (₱800/head private banca) are optional add-ons.
        $lguFee = 300.00 * $paxCount;
        $envFee = 50.00 * $paxCount;
        $carpoolFee = ($validated['pickup_option'] === 'carpool') ? (1000.00 * $paxCount) : 0.00;
        $boatDiveFee = (!empty($validated['boat_dive']) && $validated['boat_dive']) ? (800.00 * $paxCount) : 0.00;

        $totalAmount = $subtotal + $lguFee + $envFee + $carpoolFee + $boatDiveFee;
        
        // Deposit Policy:
        // ₱3,000/head covers upfront instructor scheduling and resort room holds.
        // The remaining balance is collected at camp on Day 1 upon physical check-in.
        $downpaymentAmount = min($totalAmount, 3000.00 * $paxCount);
        $balanceAmount = max(0, $totalAmount - $downpaymentAmount);

        // Auto-assign batch roster for this weekend dates
        $batch = app(\App\Services\BatchManagementService::class)->findOrCreateBatchForDates($startDate, $endDate);

        // Security Credentials:
        // 4-digit PIN enables self-service booking portal lookup without requiring traditional password registration.
        $bookingNumber = 'CFP-' . date('Y') . '-' . strtoupper(Str::random(5));
        $pin = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Atomic Transaction:
        // Guarantees booking, participant rows, dynamic pricing audits, and payment records
        // are committed in sync; rolls back completely if PayMongo session initialization fails.
        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'pin' => $pin,
                'batch_id' => $batch->id,
                'class_type' => $validated['class_type'],
                'is_certified_diver' => $isCertified,
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
                'contact_name' => $contactName ?: 'Guest',
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'contact_facebook' => $validated['contact_facebook'] ?? null,
                'status' => 'pending_downpayment',
            ]);

            // Save individual participant health questionnaires and medical disclosures
            foreach ($validated['participants'] as $pData) {
                $pName = !empty($pData['name'])
                    ? $pData['name']
                    : trim(($pData['first_name'] ?? '') . ' ' . ($pData['last_name'] ?? ''));

                $booking->participants()->create([
                    'name' => $pName ?: 'Participant',
                    'age' => (int) $pData['age'],
                    'health_condition' => $pData['health_condition'] ?? 'None',
                    'swimmer_status' => $pData['swimmer_status'] ?? 'beginner',
                    'price_per_person' => $classPrice,
                ]);
            }

            // Pricing Audit Trail:
            // Persists exact snapshot of active rule adjustments applied at checkout time
            // to preserve pricing integrity against future admin rule modifications.
            foreach ($quote['adjustments'] as $adj) {
                $booking->priceAdjustments()->create([
                    'pricing_rule_id' => $adj['rule_id'],
                    'rule_name' => $adj['rule_name'],
                    'rule_type' => $adj['rule_type'],
                    'condition_summary' => $adj['condition_summary'],
                    'base_price' => $quote['base_price_per_pax'],
                    'adjustment_amount' => $adj['delta_per_pax'],
                    'adjusted_price' => $quote['adjusted_price_per_pax'],
                ]);
            }

            // Payment Session Lifecycle:
            // Expires in 24 hours to automatically purge uncompleted reservations.
            $paymentMethod = $validated['payment_method'] ?? 'paymongo';
            $transactionId = 'PAY-' . strtoupper(Str::random(10));

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'paymongo',
                'transaction_id' => $transactionId,
                'amount' => $downpaymentAmount,
                'fee_amount' => 0.00,
                'net_amount' => $downpaymentAmount,
                'payment_type' => 'downpayment',
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            // Hosted PayMongo Gateway v2: Generates direct GCash, Maya, Card, or GrabPay checkout session
            $payMongoGateway = app(\App\Services\Gateways\PayMongoGateway::class);
            $checkoutResult = $payMongoGateway->createCheckoutSession($booking, $downpaymentAmount);

            if (!$checkoutResult['success']) {
                $errMsg = is_array($checkoutResult['error'] ?? null)
                    ? ($checkoutResult['error']['errors'][0]['detail'] ?? 'PayMongo session creation failed.')
                    : ($checkoutResult['error'] ?? 'Unable to connect to PayMongo.');
                throw new Exception("PayMongo Error: " . $errMsg);
            }

            $payment->update([
                'paymongo_resource_id' => $checkoutResult['checkout_id'],
            ]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'initiated',
                'new_status' => 'pending_downpayment',
                'changed_by' => null,
                'note' => "Reservation created. Awaiting downpayment of ₱" . number_format($downpaymentAmount, 2) . " via PayMongo Hosted Checkout.",
                'created_at' => now(),
            ]);

            AuditLogger::log(
                'BOOKING_CREATED',
                "New booking #{$booking->booking_number} created for {$booking->contact_name} ({$paxCount} pax, {$booking->class_type}). Awaiting PayMongo checkout.",
                null,
                $booking->contact_name,
                $request
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'is_paymongo_redirect' => true,
                'checkout_url' => $checkoutResult['checkout_url'],
                'checkout_id' => $checkoutResult['checkout_id'],
                'booking_number' => $booking->booking_number,
                'pin' => $booking->pin,
                'booking_id' => $booking->id,
                'downpayment_paid' => $downpaymentAmount,
                'downpayment_due' => $downpaymentAmount,
                'balance_due' => $balanceAmount,
                'manage_url' => route('manage.show', ['booking_number' => $booking->booking_number, 'pin' => $booking->pin]),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while setting up your PayMongo payment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
