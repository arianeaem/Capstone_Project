<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\BookingStatusLog;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\BookingPolicyEngine;
use App\Services\WeatherSafetyService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected WeatherSafetyService $weatherService,
        protected BookingPolicyEngine $policyEngine
    ) {}

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): View
    {
        $query = Booking::with('participants', 'payments');

        // By default, exclude unpaid downpayment draft bookings unless explicitly requested
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', '!=', 'pending_downpayment');
        }

        // Search filter (Booking #, Contact Name, Contact Phone, Email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Class type filter
        if ($request->filled('class_type')) {
            $query->where('class_type', $request->input('class_type'));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->input('date_to'));
        }

        // Payment status filter
        if ($request->filled('payment_status')) {
            $pStatus = $request->input('payment_status');
            if ($pStatus === 'paid') {
                $query->whereHas('payments', fn($p) => $p->where('status', 'completed'));
            } elseif ($pStatus === 'refunded') {
                $query->whereHas('payments', fn($p) => $p->where('status', 'refunded'));
            }
        }

        // Batch assignment filter
        if ($request->filled('batch_status')) {
            if ($request->input('batch_status') === 'unassigned') {
                $query->whereNull('batch_id');
            } elseif ($request->input('batch_status') === 'assigned') {
                $query->whereNotNull('batch_id');
            }
        }

        // Sorting (Default: Newest to Oldest)
        $sort = $request->input('sort', 'created_desc');
        match ($sort) {
            'created_desc' => $query->latest('created_at'),
            'created_asc' => $query->oldest('created_at'),
            'dive_date_asc' => $query->orderBy('start_date', 'asc')->latest('created_at'),
            'dive_date_desc' => $query->orderBy('start_date', 'desc')->latest('created_at'),
            'amount_desc' => $query->orderBy('total_amount', 'desc'),
            'amount_asc' => $query->orderBy('total_amount', 'asc'),
            'guest_asc' => $query->orderBy('contact_name', 'asc'),
            'guest_desc' => $query->orderBy('contact_name', 'desc'),
            'status' => $query->orderBy('status'),
            default => $query->latest('created_at'),
        };

        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));
        $bookings = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Booking::where('status', '!=', 'pending_downpayment')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'rescheduled' => Booking::where('status', 'rescheduled')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'no_show' => Booking::where('status', 'no_show')->count(),
            'cancelled' => Booking::whereIn('status', ['cancelled_by_camp', 'cancelled_by_guest'])->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Show the manual walk-in / phone booking creation form.
     */
    public function create(): View
    {
        $pickupPoints = [
            ['id' => 'monumento', 'name' => 'Monumento (Caloocan) - 2:30 AM'],
            ['id' => 'tiendesitas', 'name' => 'Shell Tiendesitas (Pasig) - 3:00 AM'],
            ['id' => 'market_market', 'name' => 'Market! Market! (BGC, Taguig) - 3:40 AM'],
            ['id' => 'alabang', 'name' => 'Starmall Alabang (Muntinlupa) - 4:15 AM'],
            ['id' => 'sto_tomas', 'name' => 'Sto. Tomas SLEX Exit (Batangas) - 5:30 AM'],
        ];

        return view('admin.bookings.create', compact('pickupPoints'));
    }

    /**
     * Store a manually entered walk-in / phone booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        // Merge lead contact first_name and last_name if present
        if ($request->filled('first_name') || $request->filled('last_name')) {
            $contactName = trim(($request->input('first_name') ?? '') . ' ' . ($request->input('last_name') ?? ''));
            if ($contactName !== '') {
                $request->merge(['contact_name' => $contactName]);
            }
        }

        // Merge participant first_name and last_name if present
        if ($request->has('participants') && is_array($request->input('participants'))) {
            $participants = $request->input('participants');
            foreach ($participants as $i => $p) {
                if (isset($p['first_name']) || isset($p['last_name'])) {
                    $pName = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                    if ($pName !== '') {
                        $participants[$i]['name'] = $pName;
                    }
                }
            }
            $request->merge(['participants' => $participants]);
        }

        // Sanitize phone number spacing/dashes before validation
        if ($request->has('contact_phone')) {
            $cleanedPhone = preg_replace('/[\s\-]/', '', (string)$request->input('contact_phone'));
            $request->merge(['contact_phone' => $cleanedPhone]);
        }

        $validated = $request->validate([
            'class_type' => 'required|in:discovery,fundive,refinement',
            'is_certified_diver' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'participants' => 'required|array|min:1|max:45',
            'participants.*.name' => 'required|string|min:2|max:100|regex:/^[\pL\s\.\'\-]+$/u',
            'participants.*.age' => 'required|integer|min:8|max:85',
            'participants.*.health_condition' => 'nullable|string|max:1000',
            'participants.*.swimmer_status' => 'nullable|string|max:50',
            'contact_name' => 'required|string|min:2|max:100|regex:/^[\pL\s\.\'\-]+$/u',
            'contact_email' => 'required|email:rfc,filter|max:255',
            'contact_phone' => ['required', 'string', 'regex:/^(\+?63|0)9\d{9}$/'],
            'contact_facebook' => 'nullable|string|max:255',
            'pickup_option' => 'required|in:none,own,carpool',
            'pickup_location' => 'nullable|string|max:255',
            'boat_dive' => 'boolean',
            'payment_method' => 'required|in:gcash,bpi_bank_transfer',
            'payment_stage' => 'required|in:downpayment,full',
            'payment_reference' => 'nullable|string|max:100',
            'admin_notes' => 'nullable|string|max:1000',
        ], [
            'contact_phone.regex' => 'Please enter a valid Philippine mobile number (e.g. 09171234567 or +639171234567).',
            'contact_email.email' => 'Please provide a valid email address.',
            'participants.*.age.min' => 'Participant age must be at least 8 years old.',
            'participants.*.age.max' => 'Participant age cannot exceed 85 years old.',
            'participants.*.name.regex' => 'Participant names must contain letters only.',
            'contact_name.regex' => 'Contact name must contain letters only.',
        ]);

        $participantCount = count($validated['participants']);

        // Check 45 pax batch capacity ceiling
        $startDate = $validated['start_date'];
        $existingPax = (int) Booking::whereDate('start_date', $startDate)
            ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest'])
            ->withCount('participants')
            ->get()
            ->sum('participants_count');

        $maxCapacity = 45;
        if (($existingPax + $participantCount) > $maxCapacity) {
            $remaining = max(0, $maxCapacity - $existingPax);
            return back()->withInput()->with('error', "Cannot create booking: Batch capacity ceiling of {$maxCapacity} pax reached for {$startDate} (Only {$remaining} slots available).");
        }

        $pricePerPerson = match ($validated['class_type']) {
            'discovery' => 4250.00,
            'fundive' => ($validated['is_certified_diver'] ?? false) ? 2500.00 : 3300.00,
            'refinement' => 4100.00,
        };

        $subtotal = $pricePerPerson * $participantCount;
        $carpoolFee = ($validated['pickup_option'] === 'carpool') ? (1200.00 * $participantCount) : 0.00;
        $boatDiveFee = ($validated['boat_dive'] ?? false) ? (600.00 * $participantCount) : 0.00;
        $lguFee = 300.00 * $participantCount;
        $environmentalFee = 50.00 * $participantCount;
        $totalAmount = $subtotal + $carpoolFee + $boatDiveFee + $lguFee + $environmentalFee;

        // Downpayment rule
        $downpaymentPerHead = ($validated['pickup_option'] === 'carpool') ? 3000.00 : 2000.00;
        $downpaymentAmount = min($downpaymentPerHead * $participantCount, $totalAmount);

        $paidAmount = ($validated['payment_stage'] === 'full') ? $totalAmount : $downpaymentAmount;
        $balanceAmount = $totalAmount - $paidAmount;

        // Generate unique Booking Number and 4-digit PIN
        $bookingNumber = 'CFP-' . date('Y') . '-' . str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        while (Booking::where('booking_number', $bookingNumber)->exists()) {
            $bookingNumber = 'CFP-' . date('Y') . '-' . str_pad(mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        $pin = (string) mt_rand(1000, 9999);

        $booking = DB::transaction(function () use (
            $validated,
            $bookingNumber,
            $pin,
            $pricePerPerson,
            $carpoolFee,
            $boatDiveFee,
            $lguFee,
            $environmentalFee,
            $subtotal,
            $totalAmount,
            $downpaymentAmount,
            $balanceAmount,
            $paidAmount,
            $currentUser
        ) {
            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'pin' => $pin,
                'class_type' => $validated['class_type'],
                'is_certified_diver' => $validated['is_certified_diver'] ?? false,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'pickup_option' => $validated['pickup_option'],
                'pickup_location' => $validated['pickup_location'] ?? null,
                'carpool_fee' => $carpoolFee,
                'boat_dive' => $validated['boat_dive'] ?? false,
                'boat_dive_fee' => $boatDiveFee,
                'lgu_fee' => $lguFee,
                'environmental_fee' => $environmentalFee,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'downpayment_amount' => $downpaymentAmount,
                'balance_amount' => $balanceAmount,
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'contact_facebook' => $validated['contact_facebook'] ?? null,
                'status' => 'confirmed',
                'created_by' => $currentUser->id,
            ]);

            foreach ($validated['participants'] as $p) {
                BookingParticipant::create([
                    'booking_id' => $booking->id,
                    'name' => $p['name'],
                    'age' => $p['age'],
                    'health_condition' => $p['health_condition'] ?? 'None declared',
                    'swimmer_status' => $p['swimmer_status'] ?? 'swimmer',
                    'price_per_person' => $pricePerPerson,
                ]);
            }

            // Record offline payment
            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['payment_reference'] ?: ('MANUAL-TXN-' . strtoupper(bin2hex(random_bytes(5)))),
                'amount' => $paidAmount,
                'payment_type' => $validated['payment_stage'],
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Initial Status Log
            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => 'new',
                'new_status' => 'confirmed',
                'changed_by' => $currentUser->id,
                'note' => 'Manual reservation created by ' . $currentUser->name . ($validated['admin_notes'] ? ' - Note: ' . $validated['admin_notes'] : ''),
                'created_at' => now(),
            ]);

            return $booking;
        });

        AuditLogger::log(
            'BOOKING_MANUAL_CREATED',
            "Manual booking {$booking->booking_number} created for {$booking->contact_name} by {$currentUser->name} (Amount: ₱{$booking->total_amount}, Paid: ₱{$paidAmount})",
            $currentUser,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking #{$booking->booking_number} created successfully!");
    }

    /**
     * Display full booking detail view.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['participants', 'payments', 'statusLogs.user', 'rescheduleRequests.reviewer', 'cancellationRequests.reviewer', 'createdBy']);

        $policy = $this->policyEngine->evaluate($booking);

        return view('admin.bookings.show', compact('booking', 'policy'));
    }

    /**
     * Show booking edit form (RA 10173 compliant).
     */
    public function edit(Booking $booking): View
    {
        $booking->load('participants');

        $pickupPoints = [
            ['id' => 'monumento', 'name' => 'Monumento (Caloocan) - 2:30 AM'],
            ['id' => 'tiendesitas', 'name' => 'Shell Tiendesitas (Pasig) - 3:00 AM'],
            ['id' => 'market_market', 'name' => 'Market! Market! (BGC, Taguig) - 3:40 AM'],
            ['id' => 'alabang', 'name' => 'Starmall Alabang (Muntinlupa) - 4:15 AM'],
            ['id' => 'sto_tomas', 'name' => 'Sto. Tomas SLEX Exit (Batangas) - 5:30 AM'],
        ];

        return view('admin.bookings.edit', compact('booking', 'pickupPoints'));
    }

    /**
     * Update booking and participant details with immutable audit trail.
     */
    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $currentUser = Auth::user();

        // Sanitize phone number spacing/dashes before validation
        if ($request->has('contact_phone')) {
            $cleanedPhone = preg_replace('/[\s\-]/', '', (string)$request->input('contact_phone'));
            $request->merge(['contact_phone' => $cleanedPhone]);
        }

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'pickup_option' => 'nullable|in:none,own,carpool',
            'pickup_location' => 'nullable|string|max:255',
            'boat_dive' => 'nullable|boolean',
            'contact_name' => 'required|string|min:2|max:100|regex:/^[\pL\s\.\'\-]+$/u',
            'contact_email' => 'required|email:rfc,filter|max:255',
            'contact_phone' => ['required', 'string', 'regex:/^(\+?63|0)9\d{9}$/'],
            'contact_facebook' => 'nullable|string|max:255',
            'participants' => 'required|array|min:1|max:45',
            'participants.*.id' => 'nullable|integer',
            'participants.*.name' => 'required|string|min:2|max:100|regex:/^[\pL\s\.\'\-]+$/u',
            'participants.*.age' => 'required|integer|min:8|max:85',
            'participants.*.health_condition' => 'nullable|string|max:1000',
            'participants.*.swimmer_status' => 'nullable|string|max:50',
            'edit_reason' => 'required|string|max:500',
        ], [
            'contact_phone.regex' => 'Please enter a valid Philippine mobile number (e.g. 09171234567 or +639171234567).',
            'contact_email.email' => 'Please provide a valid email address.',
            'participants.*.age.min' => 'Participant age must be at least 8 years old.',
            'participants.*.age.max' => 'Participant age cannot exceed 85 years old.',
            'participants.*.name.regex' => 'Participant names must contain letters only.',
            'contact_name.regex' => 'Contact name must contain letters only.',
        ]);

        $participantCount = count($validated['participants']);
        $originalParticipantCount = $booking->participants()->count();
        if ($participantCount !== $originalParticipantCount) {
            return back()->withInput()->with('error', 'Adding or removing participants is not permitted when editing booking details. Only existing participants can be modified.');
        }

        // Transportation option and boat dive are fixed to preserve pricing & downpayment agreement
        $pickupOption = $booking->pickup_option;
        $pickupLocation = ($pickupOption === 'carpool') ? ($validated['pickup_location'] ?? $booking->pickup_location) : null;
        $boatDive = (bool)$booking->boat_dive;

        $pricePerPerson = match ($booking->class_type) {
            'discovery' => 4250.00,
            'fundive' => $booking->is_certified_diver ? 2500.00 : 3300.00,
            'refinement' => 4100.00,
        };

        $subtotal = $pricePerPerson * $participantCount;
        $carpoolFee = ($pickupOption === 'carpool') ? (1200.00 * $participantCount) : 0.00;
        $boatDiveFee = $boatDive ? (600.00 * $participantCount) : 0.00;
        $lguFee = 300.00 * $participantCount;
        $environmentalFee = 50.00 * $participantCount;
        $totalAmount = $subtotal + $carpoolFee + $boatDiveFee + $lguFee + $environmentalFee;

        $downpaymentPerHead = ($pickupOption === 'carpool') ? 3000.00 : 2000.00;
        $downpaymentAmount = min($downpaymentPerHead * $participantCount, $totalAmount);
        $balanceAmount = $totalAmount - $booking->payments()->whereIn('status', ['completed', 'paid'])->sum('amount');

        // Track changes for immutable audit trail (RA 10173)
        $diffs = [];
        if ($booking->start_date->format('Y-m-d') !== $validated['start_date']) {
            $diffs[] = "Dates: {$booking->start_date->format('Y-m-d')} {$validated['start_date']}";
        }
        if ($booking->contact_name !== $validated['contact_name']) {
            $diffs[] = "Contact Name: {$booking->contact_name} {$validated['contact_name']}";
        }
        if ($booking->contact_email !== $validated['contact_email']) {
            $diffs[] = "Contact Email: {$booking->contact_email} {$validated['contact_email']}";
        }
        if ($booking->contact_phone !== $validated['contact_phone']) {
            $diffs[] = "Contact Phone: {$booking->contact_phone} {$validated['contact_phone']}";
        }
        if ($pickupOption === 'carpool' && $booking->pickup_location !== $pickupLocation) {
            $diffs[] = "Carpool Hub: " . ($booking->pickup_location ?: 'None') . " " . ($pickupLocation ?: 'None');
        }

        DB::transaction(function () use (
            $booking,
            $validated,
            $pickupOption,
            $pickupLocation,
            $carpoolFee,
            $boatDive,
            $boatDiveFee,
            $lguFee,
            $environmentalFee,
            $subtotal,
            $totalAmount,
            $downpaymentAmount,
            $balanceAmount,
            $currentUser,
            $diffs
        ) {
            $booking->update([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'pickup_option' => $pickupOption,
                'pickup_location' => $pickupLocation,
                'carpool_fee' => $carpoolFee,
                'boat_dive' => $boatDive,
                'boat_dive_fee' => $boatDiveFee,
                'lgu_fee' => $lguFee,
                'environmental_fee' => $environmentalFee,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
                'downpayment_amount' => $downpaymentAmount,
                'balance_amount' => max(0, $balanceAmount),
                'contact_name' => $validated['contact_name'],
                'contact_email' => $validated['contact_email'],
                'contact_phone' => $validated['contact_phone'],
                'contact_facebook' => $validated['contact_facebook'] ?? null,
            ]);

            // Update existing participants
            foreach ($validated['participants'] as $pData) {
                if (!empty($pData['id'])) {
                    $participant = BookingParticipant::find($pData['id']);
                    if ($participant && $participant->booking_id === $booking->id) {
                        $participant->update([
                            'name' => $pData['name'],
                            'age' => $pData['age'],
                            'health_condition' => $pData['health_condition'] ?? 'None declared',
                            'swimmer_status' => $pData['swimmer_status'] ?? 'swimmer',
                        ]);
                    }
                }
            }

            $noteText = 'Booking details modified by ' . $currentUser->name . ' - Reason: ' . $validated['edit_reason'];
            if (!empty($diffs)) {
                $noteText .= ' [' . implode(', ', $diffs) . ']';
            }

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $booking->status,
                'new_status' => $booking->status,
                'changed_by' => $currentUser->id,
                'note' => $noteText,
                'created_at' => now(),
            ]);
        });

        // Immutable Audit Log for Data Privacy Act (RA 10173) compliance
        AuditLogger::log(
            'BOOKING_DATA_MODIFIED',
            "Booking #{$booking->booking_number} modified by {$currentUser->name} ({$currentUser->role}). Reason: {$validated['edit_reason']}. " . (!empty($diffs) ? implode(', ', $diffs) : 'Participant information synced.'),
            $currentUser,
            $currentUser->name,
            $request
        );

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', "Booking #{$booking->booking_number} updated successfully.");
    }

    /**
     * Update booking lifecycle status.
     */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,rescheduled,no_show,cancelled_by_camp,cancelled_by_guest',
            'note' => 'nullable|string|max:500',
        ]);

        $oldStatus = $booking->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with('info', 'Status is already set to ' . $newStatus);
        }

        $note = $validated['note'] ?: "Status updated from {$oldStatus} to {$newStatus} by {$currentUser->name}";

        // Specific handling for No-show forfeiture
        if ($newStatus === 'no_show') {
            $note .= ' (No-show: Downpayment forfeited per camp policy)';
        }

        DB::transaction(function () use ($booking, $oldStatus, $newStatus, $note, $currentUser) {
            $booking->update(['status' => $newStatus]);

            BookingStatusLog::create([
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $currentUser->id,
                'note' => $note,
                'created_at' => now(),
            ]);
        });

        AuditLogger::log(
            'BOOKING_STATUS_CHANGED',
            "Booking #{$booking->booking_number} status transitioned: {$oldStatus} {$newStatus} by {$currentUser->name}. Note: {$note}",
            $currentUser,
            $currentUser->name,
            $request
        );

        $flashMessage = "Status for Booking #{$booking->booking_number} changed to {$booking->status_badge['label']}.";
        if (in_array($newStatus, ['cancelled_by_camp', 'cancelled_by_guest'])) {
            $flashMessage .= " Please review and process any pending refunds in the Payments & Refunds module.";
        }

        return back()->with('success', $flashMessage);
    }
}
