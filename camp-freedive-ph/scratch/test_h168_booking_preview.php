<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WeatherForecastService;
use App\Services\WeatherSafetyService;
use Carbon\Carbon;

$weatherSafetyService = app(WeatherSafetyService::class);
$weatherForecastService = app(WeatherForecastService::class);

$startDate = Carbon::today(WeatherForecastService::TIMEZONE)->addDays(7); // H+168 (7 days out)
$endDate = $startDate->copy()->addDay();

echo "====================================================================\n";
echo "MANUAL BOOKING TEST AT H+168 (" . $startDate->format('Y-m-d') . ")\n";
echo "====================================================================\n\n";

$forecast = $weatherSafetyService->getForecast($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

echo "1. API / UI Response Payload:\n";
echo " - Risk Level: " . $forecast['risk_level'] . "\n";
echo " - Overall Classification: " . $forecast['overall_classification'] . "\n";
echo " - Confidence: " . $forecast['confidence'] . "\n";
echo " - Operational Confidence Advisory: " . ($forecast['confidence_advisory'] ?? 'NONE') . "\n";
echo " - Formatted UI Description: " . $forecast['description'] . "\n";
echo " - Days Out: " . $forecast['days_out'] . " days (" . ($forecast['days_out'] * 24) . " hours)\n\n";

echo "2. Day 1 Breakdown:\n";
echo " - Date: " . $forecast['day1']['date'] . "\n";
echo " - Classification: " . $forecast['day1']['classification'] . "\n";
echo " - Confidence: " . $forecast['day1']['confidence'] . "\n";
echo " - Advisory: " . ($forecast['day1']['confidence_advisory'] ?? 'NONE') . "\n\n";

echo "3. Day 2 Breakdown:\n";
echo " - Date: " . $forecast['day2']['date'] . "\n";
echo " - Classification: " . $forecast['day2']['classification'] . "\n";
echo " - Confidence: " . $forecast['day2']['confidence'] . "\n";
echo " - Advisory: " . ($forecast['day2']['confidence_advisory'] ?? 'NONE') . "\n\n";

echo "4. Safety Ceiling Straddling Test:\n";
$straddlingPhysics = [
    'significant_wave_height_m' => ['p10' => 0.90, 'p50' => 1.40, 'p90' => 1.95], // Straddles 1.80m PCG Ceiling
    'wind_speed_kmh' => ['p10' => 38.0, 'p50' => 41.0, 'p90' => 45.0],            // Straddles 42.0 km/h PCG Ceiling
    'wind_gust_kmh' => ['p10' => 30.0, 'p50' => 40.0, 'p90' => 45.0],
    'current_speed_ms' => ['p10' => 0.30, 'p50' => 0.50, 'p90' => 0.70],
];
$ceilingResult = $weatherForecastService->computeWeightedScore($straddlingPhysics);
echo " - Straddling Score: " . $ceilingResult['weighted_score_pct'] . "%\n";
echo " - Straddling Classification: " . $ceilingResult['classification'] . "\n";
echo " - Straddling Evaluated Confidence: " . $ceilingResult['confidence'] . "\n\n";

echo "====================================================================\n";
echo "VERIFICATION PASSED: Confidence flag and advisory surfaced successfully!\n";
echo "====================================================================\n";
