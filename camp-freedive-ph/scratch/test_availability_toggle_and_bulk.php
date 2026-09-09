<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'coach')->first();
if (!$user) {
    echo "No coach user found\n";
    exit(0);
}

\Illuminate\Support\Facades\Auth::login($user);

$ctrl = app(\App\Http\Controllers\Coach\AvailabilityController::class);

// Test 1: Single Toggle (future date e.g. 2026-10-10)
$req1 = \Illuminate\Http\Request::create('/coach/availability/toggle', 'POST', ['date' => '2026-10-10']);
$req1->headers->set('Accept', 'application/json');
$resp1 = $ctrl->toggle($req1);
echo "Toggle Response: " . $resp1->getContent() . "\n";

// Test 2: Bulk Update (dates e.g. ['2026-10-17', '2026-10-24'], status 'available')
$req2 = \Illuminate\Http\Request::create('/coach/availability/bulk', 'POST', [
    'dates' => ['2026-10-17', '2026-10-24'],
    'status' => 'available',
]);
$req2->headers->set('Accept', 'application/json');
$resp2 = $ctrl->bulkUpdate($req2);
echo "Bulk Update (available) Response: " . $resp2->getContent() . "\n";

// Test 3: Bulk Update (dates e.g. ['2026-10-17', '2026-10-24'], status 'remove')
$req3 = \Illuminate\Http\Request::create('/coach/availability/bulk', 'POST', [
    'dates' => ['2026-10-17', '2026-10-24'],
    'status' => 'remove',
]);
$req3->headers->set('Accept', 'application/json');
$resp3 = $ctrl->bulkUpdate($req3);
echo "Bulk Update (remove) Response: " . $resp3->getContent() . "\n";
