<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'owner')->first();
Illuminate\Support\Facades\Auth::login($user);

$request = Illuminate\Http\Request::create('/admin/payments', 'GET');
$request->setUserResolver(fn() => $user);
$response = app()->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Length: " . strlen($response->getContent()) . " bytes\n";
echo "Contains 'Payments & Refunds': " . (str_contains($response->getContent(), 'Payments & Refunds') ? 'YES' : 'NO') . "\n";
