<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 24-Hour Continuous Weather & Marine Forecast Cache Sync (Every 15 min)
        $schedule->command('forecast:update --assess-batches')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/forecast_cron.log'));

        // Live Open-Meteo Weather & Marine Risk Assessment (Hourly)
        $schedule->command('weather:assess-batches')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/weather_schedule.log'));

        // Automated Nightly Forecast Accuracy Archive (Daily at 00:05)
        $schedule->command('forecast:archive-accuracy')
            ->dailyAt('00:05')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/forecast_accuracy.log'));

        // Phase 0 ML Multi-Horizon Model Re-benchmarking (Quarterly)
        $schedule->command('ml:rebenchmark')
            ->quarterly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ml_rebenchmark.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
