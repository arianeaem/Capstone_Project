<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * 24-Hour Continuous Weather & Marine Forecast Cache Sync
 * Runs every 15 minutes (96 times a day) to maintain continuous whole-day predictions in local cache
 * providing sub-millisecond (<1ms) response times for all users, booking requests, and batch risk engines.
 */
Schedule::command('forecast:update --assess-batches')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/forecast_cron.log'));

/**
 * Live Open-Meteo Weather & Marine Risk Assessment
 * Runs hourly for ongoing and upcoming batches within 16 days.
 */
Schedule::command('weather:assess-batches')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/weather_schedule.log'));

/**
 * Automated Nightly Forecast Accuracy Archive & Verification
 * Runs daily at 00:05 to compare multi-horizon predictions against realized ocean observations.
 */
Schedule::command('forecast:archive-accuracy')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/forecast_accuracy.log'));

/**
 * Automated Daily AI Demand & Revenue Forecast Retraining Pipeline
 * Runs daily at 02:00 AM (nightly) to ingest newly completed batch records,
 * re-engineer lag features, fit XGBoost regressors, and push fresh 90-day rolling forecasts.
 */
Schedule::command('ml:retrain-demand')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/ml_demand_retrain.log'));

/**
 * Automated Quarterly ML Multi-Horizon Model Re-benchmarking
 * Runs quarterly (Jan 1, Apr 1, Jul 1, Oct 1 at 00:00) to evaluate seasonal shifts (Dry vs. Wet season).
 */
Schedule::command('ml:rebenchmark')
    ->quarterly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/ml_rebenchmark.log'));




