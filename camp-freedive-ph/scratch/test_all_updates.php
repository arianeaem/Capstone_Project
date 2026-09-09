<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== 1. VERIFYING /book NEW PICKUP POINTS & UI ===\n";
$req = Illuminate\Http\Request::create('/book', 'GET');
$res = app()->handle($req);
$content = $res->getContent();

$pickupCheck = [
    'Monumento Hypermarket - 2:30 AM',
    'Shell Tiendesitas - 3:00 AM',
    'Market Market Taxi Bay - 3:40 AM',
    'Alabang Starmall - 4:15 AM',
    'Sto Tomas Exit - 5:30 AM',
];

foreach ($pickupCheck as $p) {
    echo " - Checking {$p}: " . (str_contains($content, $p) ? 'FOUND ✓' : 'MISSING ✗') . "\n";
}

echo " - Checking Descriptive Policy (> 14 Days): " . (str_contains($content, 'Notice Given More than 2 Weeks (> 14 Days)') ? 'FOUND ✓' : 'MISSING ✗') . "\n";
echo " - Checking Descriptive Policy (7 to 14 Days): " . (str_contains($content, 'Notice Given 7 to 14 Days Before Trip') ? 'FOUND ✓' : 'MISSING ✗') . "\n";
echo " - Checking Descriptive Policy (< 7 Days): " . (str_contains($content, 'Notice Given Less than 7 Days (Locked Window)') ? 'FOUND ✓' : 'MISSING ✗') . "\n";

echo "\n=== 2. VERIFYING GROUP 8 /admin (DASHBOARD) LOCK SCREEN ===\n";
$tester = User::where('email', 'group8@campfreedive.ph')->first();
Auth::login($tester);

$reqAdmin = Illuminate\Http\Request::create('/admin', 'GET');
$reqAdmin->setUserResolver(fn() => $tester);
$resAdmin = app()->handle($reqAdmin);
$adminContent = $resAdmin->getContent();
$isLocked = str_contains($adminContent, 'bawal po gr 8 di pa sya tapos hehehehhe');
echo "/admin for Group 8 => " . ($isLocked ? 'LOCKED (bawal po gr 8 di pa sya tapos hehehehhe) ✓' : 'UNLOCKED ✗') . "\n";

$reqBookings = Illuminate\Http\Request::create('/admin/bookings', 'GET');
$reqBookings->setUserResolver(fn() => $tester);
$resBookings = app()->handle($reqBookings);
echo "/admin/bookings for Group 8 => Status: " . $resBookings->getStatusCode() . " (" . ($resBookings->getStatusCode() === 200 && !str_contains($resBookings->getContent(), 'bawal') ? 'ALLOWED' : 'BLOCKED ✗') . ")\n";

$reqPayments = Illuminate\Http\Request::create('/admin/payments', 'GET');
$reqPayments->setUserResolver(fn() => $tester);
$resPayments = app()->handle($reqPayments);
echo "/admin/payments for Group 8 => Status: " . $resPayments->getStatusCode() . " (" . ($resPayments->getStatusCode() === 200 && !str_contains($resPayments->getContent(), 'bawal') ? 'ALLOWED' : 'BLOCKED ✗') . ")\n";
