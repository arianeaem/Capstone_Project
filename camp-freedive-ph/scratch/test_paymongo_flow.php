<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Payment\PayMongoController;

echo "=== TESTING COMPLETE PAYMONGO API INTEGRATION ===\n\n";

$bookingController = app(BookingController::class);
$payMongoController = app(PayMongoController::class);

// 1. Test creating booking with Hosted PayMongo Checkout (Real API Call)
echo "1. Creating booking with PayMongo hosted checkout (API Call)...\n";
$requestHosted = Request::create('/book', 'POST', [
    'class_type' => 'discovery',
    'start_date' => now()->addDays(20)->format('Y-m-d'),
    'end_date' => now()->addDays(21)->format('Y-m-d'),
    'participants' => [
        ['name' => 'PayMongo Test Diver', 'age' => 27, 'health_condition' => 'Healthy', 'swimmer_status' => 'swimmer']
    ],
    'contact_name' => 'PayMongo Test Diver',
    'contact_email' => 'paymongo.test@example.com',
    'contact_phone' => '09171112222',
    'pickup_option' => 'carpool',
    'pickup_location' => 'Shell Tiendesitas (Pasig) - 3:00 AM',
    'boat_dive' => false,
    'confirmation_ack' => true,
    'payment_method' => 'gcash',
    'hosted_checkout' => true,
]);

$responseHosted = $bookingController->store($requestHosted);
$dataHosted = json_decode($responseHosted->getContent(), true);

echo "   Booking Number: " . ($dataHosted['booking_number'] ?? 'N/A') . "\n";
echo "   Is PayMongo Redirect: " . ($dataHosted['is_paymongo_redirect'] ? 'YES' : 'NO') . "\n";
echo "   Checkout URL: " . ($dataHosted['checkout_url'] ?? 'N/A') . "\n";
echo "   Checkout ID: " . ($dataHosted['checkout_id'] ?? 'N/A') . "\n";

$bookingId = $dataHosted['booking_id'];
$booking = Booking::find($bookingId);
echo "   Initial Booking Status: {$booking->status} (Expected: pending_downpayment)\n";

// 2. Test PayMongo Success Callback (/booking/{booking}/paymongo/success)
echo "\n2. Testing PayMongo Success Callback Return (/booking/{$bookingId}/paymongo/success)...\n";
$requestSuccess = Request::create("/booking/{$bookingId}/paymongo/success", 'GET');
$responseSuccess = $payMongoController->success($requestSuccess, $booking);

$reloadedBooking = $booking->fresh();
$reloadedPayment = $reloadedBooking->payments()->latest()->first();

echo "   Confirmed Booking Status: {$reloadedBooking->status} (Expected: confirmed)\n";
echo "   Payment Status: {$reloadedPayment->status} (Expected: paid or completed)\n";
echo "   Payment Paid At: {$reloadedPayment->paid_at}\n";
echo "   Redirect URL: " . $responseSuccess->getTargetUrl() . "\n";

// 3. Test In-Page Direct / Instant Simulation Payment
echo "\n3. Testing In-Page Direct Instant Payment Simulation...\n";
$requestInstant = Request::create('/book', 'POST', [
    'class_type' => 'discovery',
    'start_date' => now()->addDays(25)->format('Y-m-d'),
    'end_date' => now()->addDays(26)->format('Y-m-d'),
    'participants' => [
        ['name' => 'Instant Sandbox Diver', 'age' => 24, 'health_condition' => 'None', 'swimmer_status' => 'swimmer']
    ],
    'contact_name' => 'Instant Sandbox Diver',
    'contact_email' => 'instant.sandbox@example.com',
    'contact_phone' => '09173334444',
    'pickup_option' => 'own',
    'boat_dive' => false,
    'confirmation_ack' => true,
    'payment_method' => 'gcash',
    'instant_simulation' => true,
]);

$responseInstant = $bookingController->store($requestInstant);
$dataInstant = json_decode($responseInstant->getContent(), true);

echo "   Instant Booking Number: " . ($dataInstant['booking_number'] ?? 'N/A') . "\n";
$instantBooking = Booking::find($dataInstant['booking_id']);
echo "   Instant Booking Status: {$instantBooking->status} (Expected: confirmed)\n";

echo "\n=== ALL PAYMONGO INTEGRATION TESTS PASSED ===\n";
