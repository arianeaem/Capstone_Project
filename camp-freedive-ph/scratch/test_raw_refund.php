<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$secretKey = config('paymongo.secret_key');
$paymentId = 'pay_pELzcEaCjEDCfVJD3zEpdwpx';

echo "Testing raw POST to https://api.paymongo.com/v1/refunds...\n";
$response = Http::withBasicAuth($secretKey, '')
    ->withoutVerifying()
    ->acceptJson()
    ->post("https://api.paymongo.com/v1/refunds", [
        'data' => [
            'attributes' => [
                'amount' => 1000, // in centavos (PHP 10.00)
                'payment_id' => $paymentId,
                'reason' => 'requested_by_customer',
                'notes' => 'Camp FreedivePH Test Refund',
            ],
        ],
    ]);

echo "Status Code: " . $response->status() . "\n";
echo "Headers:\n" . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
echo "Body:\n" . $response->body() . "\n";
