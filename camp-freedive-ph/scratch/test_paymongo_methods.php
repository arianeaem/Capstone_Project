<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PayMongoService;
use App\Models\Booking;
use App\Services\Gateways\PayMongoGateway;

$service = app(PayMongoService::class);
$gateway = app(PayMongoGateway::class);

echo "1. Testing with config payment_method_types:\n";
$configTypes = config('paymongo.payment_method_types');
print_r($configTypes);

$res = $service->createCheckoutSession([
    [
        'name' => 'Camp FreedivePH - Test Downpayment',
        'amount' => 300000,
        'quantity' => 1,
        'currency' => 'PHP',
    ]
], [
    'description' => 'Test Booking Payment Method Types',
    'payment_method_types' => $configTypes,
    'success_url' => 'http://localhost:8000/booking/1/paymongo/success',
    'cancel_url' => 'http://localhost:8000/booking/1/paymongo/cancel',
]);

echo "\nResult 1:\n";
print_r($res);

if (!empty($res['checkout_id'])) {
    echo "\nRetrieving checkout session with getCheckoutSession({$res['checkout_id']})...\n";
    $retrieved = $service->getCheckoutSession($res['checkout_id']);
    print_r($retrieved);
}

// 2. Test with real booking
$booking = Booking::latest()->first();
if ($booking) {
    echo "\n2. Testing Gateway with latest booking #{$booking->booking_number}:\n";
    $gwRes = $gateway->createCheckoutSession($booking, 3000.00);
    print_r($gwRes);
}
