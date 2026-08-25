<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/book', 'GET');
$response = app()->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Length: " . strlen($response->getContent()) . " bytes\n";
echo "Contains 'Choose Your Freediving Class': " . (str_contains($response->getContent(), 'Choose Your Freediving Class') ? 'YES' : 'NO') . "\n";
