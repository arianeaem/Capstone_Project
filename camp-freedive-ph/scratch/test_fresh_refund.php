<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PayMongoService;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\RefundRequest;
use App\Http\Controllers\Admin\RefundController;
use Illuminate\Http\Request;

$service = app(PayMongoService::class);

echo "=== TEST: CREATE BOOKING & CHECKOUT SESSION ===\n";
// 1. Create a real checkout session
$sessionRes = $service->createCheckoutSession([
    [
        'name' => 'Camp FreedivePH - Test Refund Flow',
        'amount' => 300000, // ₱3,000.00
        'quantity' => 1,
        'currency' => 'PHP',
    ]
], [
    'description' => 'Test Booking For Refund Flow',
    'payment_method_types' => ['gcash', 'paymaya', 'card', 'qrph'],
    'success_url' => 'http://localhost:8000/booking/1/paymongo/success',
    'cancel_url' => 'http://localhost:8000/booking/1/paymongo/cancel',
]);

echo "Checkout Session ID: " . $sessionRes['checkout_id'] . "\n";
echo "Checkout URL: " . $sessionRes['checkout_url'] . "\n";

$session = $service->getCheckoutSession($sessionRes['checkout_id']);
echo "Session Payments Count: " . count($session['data']['attributes']['payments'] ?? []) . "\n";

