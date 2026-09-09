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

$portalCtrl = app(\App\Http\Controllers\Coach\PortalController::class);
$dashView = $portalCtrl->index()->render();
echo "Dashboard view rendered successfully (" . strlen($dashView) . " bytes)\n";

$scheduleCtrl = app(\App\Http\Controllers\Coach\ScheduleController::class);
$req = request();
$schedView = $scheduleCtrl->index($req)->render();
echo "Schedule view rendered successfully (" . strlen($schedView) . " bytes)\n";

$availCtrl = app(\App\Http\Controllers\Coach\AvailabilityController::class);
$availView = $availCtrl->index($req)->render();
echo "Availability view rendered successfully (" . strlen($availView) . " bytes)\n";

$reqCtrl = app(\App\Http\Controllers\Coach\RequestController::class);
$reqView = $reqCtrl->index($req)->render();
echo "Requests view rendered successfully (" . strlen($reqView) . " bytes)\n";
