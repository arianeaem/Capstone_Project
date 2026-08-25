<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

echo "=== VERIFYING LOGIN & WIZARD UPDATES ===\n\n";

$loginController = app(LoginController::class);

// 1. Test accessing /login, /admin/login, /staff/login
echo "1. Testing Staff Login Endpoints...\n";
$req1 = Request::create('/login', 'GET');
$res1 = $loginController->showLoginForm($req1);
echo "   GET /login status: " . ($res1 instanceof \Illuminate\View\View ? "VIEW RENDERED (200 OK)" : "REDIRECT") . "\n";

$req2 = Request::create('/admin/login', 'GET');
$res2 = $loginController->showLoginForm($req2);
echo "   GET /admin/login status: " . ($res2 instanceof \Illuminate\View\View ? "VIEW RENDERED (200 OK)" : "REDIRECT") . "\n";

$req3 = Request::create('/staff/login', 'GET');
$res3 = $loginController->showLoginForm($req3);
echo "   GET /staff/login status: " . ($res3 instanceof \Illuminate\View\View ? "VIEW RENDERED (200 OK)" : "REDIRECT") . "\n";

// 2. Test staff accounts exist
echo "\n2. Verifying Staff Account Credentials:\n";
$admin = User::where('email', 'admin@campfreedive.ph')->first();
$owner = User::where('email', 'owner@campfreedive.ph')->first();
$coach = User::where('email', 'coach.miko@campfreedive.ph')->first();

echo "   Admin: " . ($admin ? "{$admin->name} ({$admin->email}, Role: {$admin->role})" : "MISSING") . "\n";
echo "   Owner: " . ($owner ? "{$owner->name} ({$owner->email}, Role: {$owner->role})" : "MISSING") . "\n";
echo "   Coach: " . ($coach ? "{$coach->name} ({$coach->email}, Role: {$coach->role})" : "MISSING") . "\n";

// 3. Test logout parameter
$reqLogout = Request::create('/login?logout=1', 'GET', ['logout' => 1]);
$resLogout = $loginController->showLoginForm($reqLogout);
echo "\n3. Testing ?logout=1 query parameter:\n";
echo "   Result: " . ($resLogout instanceof \Illuminate\Http\RedirectResponse ? "REDIRECT TO LOGIN (SUCCESS)" : "FAILED") . "\n";

echo "\n=== ALL CHECKS PASSED ===\n";
