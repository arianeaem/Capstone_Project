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
 * Phase 0 ML Multi-Horizon Model Re-benchmarking
 * Runs quarterly to re-evaluate AutoGluon, Chronos-2, and XGBoost predictors on latest trailing data.
 */
Schedule::command('ml:rebenchmark')
    ->quarterly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/ml_rebenchmark.log'));



