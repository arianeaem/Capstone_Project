<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PayMongoService;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\RefundRequest;

$service = app(PayMongoService::class);

echo "=== TEST: REFUND LOGIC ENHANCEMENT ===\n";

// Scenario 1: Real already-refunded payment
$alreadyRefundedId = 'pay_pELzcEaCjEDCfVJD3zEpdwpx';
echo "\n1. Testing refund on already refunded PayMongo payment ({$alreadyRefundedId}):\n";
$paymentData = $service->getPayment($alreadyRefundedId);
$existingRefunds = $paymentData['data']['attributes']['refunds'] ?? [];
echo "   Found " . count($existingRefunds) . " existing refund(s) on PayMongo.\n";
if (!empty($existingRefunds)) {
    echo "   Latest Refund ID on PayMongo: " . end($existingRefunds)['id'] . "\n";
}

// Scenario 2: Checkout Session with payment resolution
$csId = 'cs_f7f4bb93f0b7fb4407281a17';
echo "\n2. Testing resolving payment_id from Checkout Session ({$csId}):\n";
$cs = $service->getCheckoutSession($csId);
$csPayments = $cs['data']['attributes']['payments'] ?? [];
echo "   Payments count in session: " . count($csPayments) . "\n";
if (!empty($csPayments)) {
    echo "   Resolved Payment ID: " . $csPayments[0]['id'] . "\n";
}
