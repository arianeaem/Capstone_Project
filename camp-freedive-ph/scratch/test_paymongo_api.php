<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PayMongoService;
use App\Models\Booking;

$service = app(PayMongoService::class);

echo "Testing PayMongo Checkout Session API with key: " . substr(config('paymongo.secret_key'), 0, 10) . "...\n";

$result = $service->createCheckoutSession([
    [
        'name' => 'Camp FreedivePH - Discovery Class Downpayment',
        'amount' => 300000, // PHP 3,000.00
        'quantity' => 1,
        'currency' => 'PHP',
        'description' => 'Test Booking Downpayment'
    ]
], [
    'description' => 'Test Booking #CFP-TEST',
    'payment_method_types' => ['gcash', 'dob', 'card', 'paymaya', 'grab_pay', 'qrph'],
    'success_url' => 'http://localhost:8000/booking/1/paymongo/success',
    'cancel_url' => 'http://localhost:8000/booking/1/paymongo/cancel',
]);

echo "Result:\n";
print_r($result);
