<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WeatherForecastService;
use Carbon\Carbon;

$service = app(WeatherForecastService::class);
$refUpdate = new ReflectionMethod($service, 'updateAllForecasts');
$refUpdate->setAccessible(true);

$master = $refUpdate->invoke($service, 16);
echo "Master cache updated successfully with " . count($master['daily_summaries']) . " days.\n\n";

echo sprintf("%-12s | %-15s | %-10s | %-12s | %-12s | %-10s | %-10s\n", "Date", "Daytime Class", "Score %", "Avg Wind", "Max Gust", "Avg Wave", "Avg Current");
echo "----------------------------------------------------------------------------------------------------\n";

foreach ($master['daily_summaries'] as $dateKey => $summary) {
    echo sprintf(
        "%-12s | %-15s | %8.1f%% | %8.1f km/h | %8.1f km/h | %7.2f m | %7.2f m/s\n",
        $dateKey,
        $summary['daytime_classification'] ?? 'N/A',
        $summary['daytime_score_pct'] ?? 0,
        $summary['avg_wind_speed'] ?? 0,
        $summary['max_wind_speed'] ?? 0,
        $summary['avg_wave_height'] ?? 0,
        $summary['hourly'][12]['ocean_current'] ?? 0
    );
}
