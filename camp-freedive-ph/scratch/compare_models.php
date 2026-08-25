<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$lat = 13.7481;
$lon = 120.9408;

echo "=== TESTING EXPLICIT MODEL COMPARISON (MABINI, BATANGAS) ===\n\n";

// 1. Weather Models Comparison: ECMWF IFS vs NOAA GFS vs JMA vs ICON
$weatherModels = ['ecmwf_ifs025', 'gfs_seamless', 'jma_seamless', 'icon_seamless'];

foreach ($weatherModels as $model) {
    $res = Http::withoutVerifying()->get('https://api.open-meteo.com/v1/forecast', [
        'latitude' => $lat,
        'longitude' => $lon,
        'timezone' => 'Asia/Manila',
        'forecast_days' => 1,
        'models' => $model,
        'hourly' => 'wind_speed_10m,pressure_msl,rain',
    ]);
    
    $json = $res->json();
    $wind = $json['hourly']['wind_speed_10m'][12] ?? 'N/A'; // Midday (12:00 PHT)
    $rain = $json['hourly']['rain'][12] ?? 'N/A';
    $pressure = $json['hourly']['pressure_msl'][12] ?? 'N/A';
    
    echo "Model: {$model}\n";
    echo "  - Grid Lat/Lon: {$json['latitude']} / {$json['longitude']}\n";
    echo "  - Midday 12:00 PHT Wind Speed: {$wind} km/h | Rain: {$rain} mm | Pressure: {$pressure} hPa\n\n";
}

// 2. Marine Models Comparison: Best Match vs ECMWF WAM vs GFS Wave
$marineModels = ['best_match', 'ecmwf_wam025', 'gfs_wave025'];

foreach ($marineModels as $mModel) {
    $params = [
        'latitude' => $lat,
        'longitude' => $lon,
        'timezone' => 'Asia/Manila',
        'forecast_days' => 1,
        'hourly' => 'wave_height,wave_period',
    ];
    if ($mModel !== 'best_match') {
        $params['models'] = $mModel;
    }
    
    $resM = Http::withoutVerifying()->get('https://marine-api.open-meteo.com/v1/marine', $params);
    $jsonM = $resM->json();
    $wave = $jsonM['hourly']['wave_height'][12] ?? 'N/A';
    $period = $jsonM['hourly']['wave_period'][12] ?? 'N/A';
    
    echo "Marine Model: {$mModel}\n";
    echo "  - Midday 12:00 PHT Wave Height: {$wave} m | Wave Period: {$period} s\n\n";
}

echo "=== COMPARISON COMPLETE ===\n";
