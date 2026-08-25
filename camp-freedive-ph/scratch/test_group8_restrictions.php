<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$tester = User::where('email', 'group8@campfreedive.ph')->first();
Auth::login($tester);

echo "=== TESTING ACCESS FOR GROUP 8 TESTER ({$tester->email}) ===\n\n";

$testUrls = [
    '/book' => 'ALLOWED (Public Customer)',
    '/manage-booking' => 'ALLOWED (Public Customer)',
    '/admin/bookings' => 'ALLOWED (Booking Management)',
    '/admin/bookings/requests' => 'ALLOWED (Booking Requests)',
    '/admin/payments' => 'ALLOWED (Payments & Refunds)',
    '/admin/payments/refunds' => 'ALLOWED (Pending Refunds)',
    '/admin/batches' => 'LOCKED (Should show custom lock screen)',
    '/admin/weather' => 'LOCKED (Should show custom lock screen)',
    '/admin/coaches' => 'LOCKED (Should show custom lock screen)',
    '/admin/settings/users' => 'LOCKED (Should show custom lock screen)',
];

foreach ($testUrls as $path => $expected) {
    $req = Illuminate\Http\Request::create($path, 'GET');
    $req->setUserResolver(fn() => $tester);
    $res = app()->handle($req);
    $content = $res->getContent();
    
    $isLockScreen = str_contains($content, 'bawal po gr 8 di pa sya tapos hehehehhe');
    
    echo sprintf("%-30s [%d] %s => %s\n", 
        $path, 
        $res->getStatusCode(), 
        $isLockScreen ? '🔒 RESTRICTED LOCK SCREEN' : '✓ NORMAL ACCESS',
        $expected
    );
}

echo "\n--- TESTING AS OWNER (Should NOT be locked anywhere) ---\n";
$owner = User::where('role', 'owner')->first();
Auth::login($owner);

$req = Illuminate\Http\Request::create('/admin/batches', 'GET');
$req->setUserResolver(fn() => $owner);
$res = app()->handle($req);
echo sprintf("/admin/batches (Owner) => %s\n", str_contains($res->getContent(), 'bawal po gr 8') ? 'LOCKED (BUG!)' : '✓ FULL OWNER ACCESS');
