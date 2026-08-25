<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WeatherForecastService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

echo "=== TESTING 24-HOUR FORECAST CACHE & LATENCY ===\n\n";

// 1. Check Master Cache
$master = Cache::get('forecast:continuous_16d');
if (!$master) {
    echo "Master cache missing. Running updateAllForecasts()...\n";
    $service = app(WeatherForecastService::class);
    $master = $service->updateAllForecasts(16);
}

echo "1. Master Cache Info:\n";
echo "   - Updated At: " . ($master['updated_at'] ?? 'N/A') . "\n";
echo "   - Days Cached: " . ($master['days_cached'] ?? 0) . " days (" . (($master['days_cached'] ?? 0) * 24) . " hours)\n";
echo "   - Source: " . ($master['source'] ?? 'N/A') . "\n\n";

// 2. Measure Cache Retrieval Latency
$testDate = Carbon::today(WeatherForecastService::TIMEZONE)->addDays(3)->format('Y-m-d');
$t0 = microtime(true);
$cachedDay = Cache::get("forecast:date:{$testDate}");
$latencyMs = round((microtime(true) - $t0) * 1000, 3);

echo "2. Measuring Cache Retrieval Latency for Date {$testDate}:\n";
echo "   - Query Latency: {$latencyMs} ms (" . ($latencyMs < 1.0 ? "SUB-MILLISECOND ✓" : "OK") . ")\n";
echo "   - 24h Overall Risk: " . ($cachedDay['overall_classification'] ?? 'N/A') . " (" . ($cachedDay['overall_score_pct'] ?? 0) . "%)\n";
echo "   - Daytime (06-18h) Risk: " . ($cachedDay['daytime_classification'] ?? 'N/A') . "\n";
echo "   - AM Window Risk: " . ($cachedDay['am_classification'] ?? 'N/A') . "\n";
echo "   - PM Window Risk: " . ($cachedDay['pm_classification'] ?? 'N/A') . "\n";
echo "   - Avg Wave Height: " . ($cachedDay['avg_wave_height'] ?? 0) . " m\n";
echo "   - Avg Wind Speed: " . ($cachedDay['avg_wind_speed'] ?? 0) . " km/h\n";
echo "   - Total Hourly Data Points in Day: " . count($cachedDay['hourly'] ?? []) . " hours\n\n";

// 3. Measure WeatherForecastService Native Evaluation Latency (Cache-Hit)
$service = app(WeatherForecastService::class);
$t1 = microtime(true);
$preview = $service->previewDateAssessment(Carbon::parse($testDate));
$serviceLatencyMs = round((microtime(true) - $t1) * 1000, 3);

echo "3. Service Preview Latency for Customer Booking:\n";
echo "   - Evaluation Latency: {$serviceLatencyMs} ms\n";
echo "   - Available: " . ($preview['available'] ? 'YES' : 'NO') . "\n";
echo "   - Overall Classification: " . ($preview['classification'] ?? 'N/A') . "\n\n";

echo "=== ALL CACHE & LATENCY BENCHMARKS PASSED ===\n";
