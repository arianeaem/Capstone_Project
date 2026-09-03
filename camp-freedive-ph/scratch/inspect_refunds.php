<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\RefundRequest;
use App\Services\PayMongoService;

$service = app(PayMongoService::class);

echo "=== PAYMENTS IN DATABASE ===\n";
$payments = Payment::latest()->take(10)->get();
foreach ($payments as $p) {
    echo "Payment #{$p->id} | Booking #{$p->booking_id} | Status: {$p->status} | Method: {$p->payment_method}\n";
    echo "   Amount: ₱{$p->amount} | Resource ID: {$p->paymongo_resource_id} | Payment ID: {$p->paymongo_payment_id} | Refund ID: {$p->paymongo_refund_id}\n";
    
    if ($p->paymongo_resource_id) {
        $session = $service->getCheckoutSession($p->paymongo_resource_id);
        if ($session) {
            $sessPayments = $session['data']['attributes']['payments'] ?? [];
            echo "   [PayMongo API Session Status]: " . ($session['data']['attributes']['status'] ?? 'unknown') . "\n";
            echo "   [PayMongo API Payments Count]: " . count($sessPayments) . "\n";
            if (!empty($sessPayments)) {
                foreach ($sessPayments as $idx => $sp) {
                    echo "      Payment [{$idx}]: ID={$sp['id']} | Status=" . ($sp['attributes']['status'] ?? 'N/A') . " | Amount=" . (($sp['attributes']['amount'] ?? 0) / 100) . "\n";
                }
            }
        }
    }
    echo "\n";
}

echo "=== REFUND REQUESTS IN DATABASE ===\n";
$refunds = RefundRequest::all();
foreach ($refunds as $r) {
    $p = $r->payment;
    echo "RefundRequest #{$r->id} | Booking #{$r->booking_id} | Payment #{$r->payment_id} | Status: {$r->status}\n";
    echo "   Amount to refund: ₱{$r->refund_amount}\n";
    echo "   Payment method: {$p->payment_method} | Payment status: {$p->status} | Paymongo Payment ID: {$p->paymongo_payment_id}\n";
    echo "   Paymongo Resource ID: {$p->paymongo_resource_id} | Transaction ID: {$p->transaction_id}\n\n";
}
