<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role', 'owner')->first();
Illuminate\Support\Facades\Auth::login($user);

$request = Illuminate\Http\Request::create('/admin/batches/create', 'GET');
$request->setUserResolver(fn() => $user);
$response = app()->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Length: " . strlen($response->getContent()) . " bytes\n";
echo "Contains 'Create 2D1N Batch Schedule': " . (str_contains($response->getContent(), 'Create 2D1N Batch Schedule') ? 'YES' : 'NO') . "\n";
echo "Contains 'Batch 7' (or sequential next batch): " . (str_contains($response->getContent(), 'Batch 7') ? 'YES' : 'NO') . "\n";
