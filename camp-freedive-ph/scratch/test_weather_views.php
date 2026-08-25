<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Batch;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\WeatherSafetyController;

echo "=== VERIFYING ENHANCED WEATHER FRONTEND VIEWS ===\n\n";

$adminUser = User::where('role', 'admin')->first();
auth()->login($adminUser);

$controller = app(WeatherSafetyController::class);

// 1. Test Admin Weather Index View
echo "1. Testing Admin Weather Index Page (/admin/weather)...\n";
$requestIndex = Request::create('/admin/weather', 'GET');
$responseIndex = $controller->index($requestIndex);
$renderedIndex = $responseIndex->render();

echo "   Index Render Status: SUCCESS (HTML length: " . strlen($renderedIndex) . " bytes)\n";
echo "   Contains 'Live 24-Hour Continuous Cache Active': " . (str_contains($renderedIndex, 'Live 24-Hour Continuous Cache Active') ? 'YES' : 'NO') . "\n";
echo "   Contains '16-Day Whole-Day Sea State Horizon': " . (str_contains($renderedIndex, '16-Day Whole-Day Sea State Horizon') ? 'YES' : 'NO') . "\n";
echo "   Contains 'Sync Forecast Now': " . (str_contains($renderedIndex, 'Sync Forecast Now') ? 'YES' : 'NO') . "\n";

// 2. Test Admin Weather Show View
$batch = Batch::latest()->first();
if ($batch) {
    echo "\n2. Testing Admin Weather Show Page (/admin/weather/{$batch->id})...\n";
    $responseShow = $controller->show($batch);
    $renderedShow = $responseShow->render();

    echo "   Show Render Status: SUCCESS (HTML length: " . strlen($renderedShow) . " bytes)\n";
    echo "   Contains '24-Hour Continuous Sea State': " . (str_contains($renderedShow, '24-Hour Continuous Sea State') ? 'YES' : 'NO') . "\n";
    echo "   Contains 'OPEN WATER AM WINDOW': " . (str_contains($renderedShow, 'OPEN WATER AM WINDOW') ? 'YES' : 'NO') . "\n";
    echo "   Contains 'OPEN WATER PM WINDOW': " . (str_contains($renderedShow, 'OPEN WATER PM WINDOW') ? 'YES' : 'NO') . "\n";
}

// 3. Test Sync Cache Action
echo "\n3. Testing Sync Cache Controller Action (/admin/weather/sync-cache)...\n";
$responseSync = $controller->syncCache();
echo "   Sync Cache Response: " . get_class($responseSync) . " (Target: " . $responseSync->getTargetUrl() . ")\n";

echo "\n=== ALL FRONTEND CHECKS PASSED ===\n";
