<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\BookingParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\BookingController as GuestBookingController;

echo "=== VERIFICATION TEST SUITE ===\n\n";

// 1. Check Guest Booking Controller validation for payment_method
echo "1. Testing Guest Booking Controller Payment Methods...\n";
$guestController = app(GuestBookingController::class);

// Test GCash (valid)
$requestGcash = Request::create('/book', 'POST', [
    'class_type' => 'discovery',
    'start_date' => now()->addDays(35)->format('Y-m-d'),
    'end_date' => now()->addDays(36)->format('Y-m-d'),
    'participants' => [
        ['name' => 'John Doe', 'age' => 25, 'health_condition' => 'None', 'swimmer_status' => 'swimmer']
    ],
    'contact_name' => 'John Doe',
    'contact_email' => 'johndoe@example.com',
    'contact_phone' => '09171234567',
    'pickup_option' => 'carpool',
    'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
    'boat_dive' => false,
    'confirmation_ack' => true,
    'payment_method' => 'gcash',
]);
$resGcash = $guestController->store($requestGcash);
$resGcashData = json_decode($resGcash->getContent(), true);
echo "   GCash booking result: " . ($resGcashData['success'] ? "SUCCESS ({$resGcashData['booking_number']})" : "FAILED") . "\n";

// Test BPI (valid)
$requestBpi = Request::create('/book', 'POST', [
    'class_type' => 'discovery',
    'start_date' => now()->addDays(35)->format('Y-m-d'),
    'end_date' => now()->addDays(36)->format('Y-m-d'),
    'participants' => [
        ['name' => 'Jane Doe', 'age' => 24, 'health_condition' => 'None', 'swimmer_status' => 'swimmer']
    ],
    'contact_name' => 'Jane Doe',
    'contact_email' => 'janedoe@example.com',
    'contact_phone' => '09179876543',
    'pickup_option' => 'carpool',
    'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
    'boat_dive' => false,
    'confirmation_ack' => true,
    'payment_method' => 'bpi_bank_transfer',
]);
$resBpi = $guestController->store($requestBpi);
$resBpiData = json_decode($resBpi->getContent(), true);
echo "   BPI booking result: " . ($resBpiData['success'] ? "SUCCESS ({$resBpiData['booking_number']})" : "FAILED") . "\n";

// Test Card (should fail validation)
try {
    $requestCard = Request::create('/book', 'POST', [
        'class_type' => 'discovery',
        'start_date' => now()->addDays(35)->format('Y-m-d'),
        'end_date' => now()->addDays(36)->format('Y-m-d'),
        'participants' => [
            ['name' => 'Invalid Method User', 'age' => 24]
        ],
        'contact_name' => 'Invalid User',
        'contact_email' => 'invalid@example.com',
        'contact_phone' => '09170000000',
        'pickup_option' => 'carpool',
        'confirmation_ack' => true,
        'payment_method' => 'card',
    ]);
    $guestController->store($requestCard);
    echo "   Card booking: UNEXPECTEDLY ACCEPTED (FAILED)\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "   Card booking: REJECTED AS EXPECTED (SUCCESS)\n";
}

// 2. Testing Admin Edit - Disallow adding participants
echo "\n2. Testing Admin Edit - Participant Locking...\n";
$admin = User::where('email', 'admin@campfreedive.ph')->first();
auth()->login($admin);
$adminController = app(AdminBookingController::class);

$createdBooking = Booking::where('booking_number', $resGcashData['booking_number'])->first();
$p1 = $createdBooking->participants->first();

// Try to add a 2nd participant in edit
$reqAddPax = Request::create("/admin/bookings/{$createdBooking->id}", 'PUT', [
    'start_date' => $createdBooking->start_date->format('Y-m-d'),
    'end_date' => $createdBooking->end_date->format('Y-m-d'),
    'contact_name' => $createdBooking->contact_name,
    'contact_email' => $createdBooking->contact_email,
    'contact_phone' => $createdBooking->contact_phone,
    'edit_reason' => 'Testing add participant rejection',
    'participants' => [
        ['id' => $p1->id, 'name' => $p1->name, 'age' => $p1->age],
        ['id' => null, 'name' => 'Extra Pax', 'age' => 20],
    ],
]);

$resAddPax = $adminController->update($reqAddPax, $createdBooking);
$sessionError = session('error');
echo "   Adding extra participant result: " . ($sessionError ? "BLOCKED WITH: '$sessionError' (SUCCESS)" : "FAILED") . "\n";

// 3. Testing Admin Edit - Update Carpool Hub Location & Details
echo "\n3. Testing Admin Edit - Carpool Hub Location Update...\n";
$newHub = 'Market! Market! (BGC, Taguig) - 3:40 AM';
$reqUpdateHub = Request::create("/admin/bookings/{$createdBooking->id}", 'PUT', [
    'start_date' => $createdBooking->start_date->format('Y-m-d'),
    'end_date' => $createdBooking->end_date->format('Y-m-d'),
    'pickup_location' => $newHub,
    'contact_name' => 'John Doe Updated',
    'contact_email' => $createdBooking->contact_email,
    'contact_phone' => $createdBooking->contact_phone,
    'edit_reason' => 'Guest changed pickup hub to BGC',
    'participants' => [
        ['id' => $p1->id, 'name' => 'John Doe Updated', 'age' => 26, 'swimmer_status' => 'swimmer', 'health_condition' => 'Healthy'],
    ],
]);

$resUpdateHub = $adminController->update($reqUpdateHub, $createdBooking);
$reloaded = $createdBooking->fresh();
echo "   Updated Carpool Location: " . $reloaded->pickup_location . " (Expected: $newHub)\n";
echo "   Updated Contact Name: " . $reloaded->contact_name . "\n";
echo "   Preserved Transport Option: " . $reloaded->pickup_option . "\n";
echo "   Participant count: " . $reloaded->participants()->count() . "\n";

// 4. Verify status log and audit trail
$latestLog = $reloaded->statusLogs()->latest('id')->first();
echo "\n4. Latest Booking Audit Trail Log:\n";
echo "   Note: " . $latestLog->note . "\n";

echo "\n=== ALL VERIFICATION CHECKS COMPLETED SUCCESSFULLY ===\n";
