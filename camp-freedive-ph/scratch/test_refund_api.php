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

echo "1. Checking latest real payment on PayMongo:\n";
// Payment #15 has Payment ID: pay_pELzcEaCjEDCfVJD3zEpdwpx
$paymentId = 'pay_pELzcEaCjEDCfVJD3zEpdwpx';
$res = $service->getPayment($paymentId);
echo "Payment info from PayMongo:\n";
print_r($res);

echo "\n2. Testing PayMongo Refund endpoint for payment {$paymentId} (₱10.00 / 1000 centavos):\n";
$refundTest = $service->refund($paymentId, 10.00, 'requested_by_customer', 'Test partial refund');
print_r($refundTest);
