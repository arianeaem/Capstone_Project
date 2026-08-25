<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$lat = 13.7481;
$lon = 120.9408;

echo "=== INSPECTING OPEN-METEO DATA SOURCES & MODELS ===\n\n";

// 1. Inspect Marine API
echo "1. Querying Marine API (Default 'Best Match')...\n";
$resMarine = Http::withoutVerifying()->get('https://marine-api.open-meteo.com/v1/marine', [
    'latitude' => $lat,
    'longitude' => $lon,
    'timezone' => 'Asia/Manila',
    'forecast_days' => 1,
    'hourly' => 'wave_height,wave_period,swell_wave_height,wind_wave_height,ocean_current_velocity',
]);
$dataMarine = $resMarine->json();
unset($dataMarine['hourly']); // remove large array for cleaner metadata inspection
print_r($dataMarine);

// 2. Inspect Atmospheric Weather API
echo "\n2. Querying Weather API (Default 'Best Match')...\n";
$resWeather = Http::withoutVerifying()->get('https://api.open-meteo.com/v1/forecast', [
    'latitude' => $lat,
    'longitude' => $lon,
    'timezone' => 'Asia/Manila',
    'forecast_days' => 1,
    'hourly' => 'rain,pressure_msl,wind_speed_10m,wind_direction_10m',
]);
$dataWeather = $resWeather->json();
unset($dataWeather['hourly']); // remove large array for cleaner metadata inspection
print_r($dataWeather);

echo "\n=== FINISHED ===\n";
