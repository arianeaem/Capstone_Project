<?php

use App\Http\Controllers\Api\MLSyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Secure API endpoints for Machine Learning pipeline integration,
| continuous retraining, and forecast synchronization.
|
*/

Route::middleware('auth.ml_token')->prefix('v1/ml')->group(function () {
    Route::get('/training-data', [MLSyncController::class, 'exportTrainingData']);
    Route::post('/sync-forecast', [MLSyncController::class, 'importForecast']);
});
