<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\DemandForecast;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MLSyncController extends Controller
{
    /**
     * Export completed historical batch summaries for ML training pipeline.
     * GET /api/v1/ml/training-data
     */
    public function exportTrainingData(Request $request): JsonResponse
    {
        $statusFilter = $request->query('status'); // e.g. 'completed' or null for all historical
        $includeAll = $request->boolean('include_all', false);

        $query = Batch::with(['bookings.participants', 'bookings.payments', 'coachAssignments'])
            ->orderBy('start_date', 'asc');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        } elseif (!$includeAll) {
            // Default to completed batches, or confirmed batches with dive history
            $query->whereIn('status', ['completed', 'confirmed']);
        }

        $batches = $query->get();

        $trainingData = $batches->map(function (Batch $batch) {
            $validBookings = $batch->bookings->whereNotIn('status', [
                'cancelled_by_camp',
                'cancelled_by_guest',
                'cancelled',
                'pending_downpayment',
            ]);

            $totalParticipants = $validBookings->sum(fn($b) => $b->participants->count());
            $grossRevenue = (float) $validBookings->sum(fn($b) => (float) $b->total_amount);
            $lguFees = (float) $validBookings->sum(fn($b) => (float) ($b->lgu_fee ?? 0));
            $envFees = (float) $validBookings->sum(fn($b) => (float) ($b->environmental_fee ?? 0));
            $carpoolFees = (float) $validBookings->sum(fn($b) => (float) ($b->carpool_fee ?? 0));
            $boatDiveFees = (float) $validBookings->sum(fn($b) => (float) ($b->boat_dive_fee ?? 0));

            // Pure freediving class revenue only (excluding carpool transportation, boat dive add-on, LGU pass, and environmental fees)
            $pureClassRevenue = (float) $validBookings->sum(fn($b) => (float) ($b->subtotal ?? 0));
            if ($pureClassRevenue <= 0 && $grossRevenue > 0) {
                $pureClassRevenue = max(0, $grossRevenue - ($lguFees + $envFees + $carpoolFees + $boatDiveFees));
            }

            $totalRevenue = $pureClassRevenue;
            $collectedRevenue = (float) $validBookings->flatMap->payments
                ->where('status', 'verified')
                ->sum(fn($p) => (float) $p->amount);


            $classBreakdown = [];
            foreach ($validBookings as $b) {
                $type = strtolower($b->class_type ?: 'discovery');
                $classBreakdown[$type] = ($classBreakdown[$type] ?? 0) + $b->participants->count();
            }

            $pickupBreakdown = [];
            foreach ($validBookings as $b) {
                $opt = strtolower($b->pickup_option ?: 'own_transpo');
                $pickupBreakdown[$opt] = ($pickupBreakdown[$opt] ?? 0) + $b->participants->count();
            }

            $startDate = $batch->start_date ? Carbon::parse($batch->start_date) : null;
            $endDate = $batch->end_date ? Carbon::parse($batch->end_date) : null;
            $month = $startDate ? $startDate->month : null;

            // Determine seasonal classification (Peak: Dec - May, Off-Peak: Jun - Oct, Shoulder: Nov)
            $seasonPeriod = 'Off-Peak';
            if ($month && in_array($month, [12, 1, 2, 3, 4, 5])) {
                $seasonPeriod = 'Peak';
            } elseif ($month && in_array($month, [10, 11])) {
                $seasonPeriod = 'Shoulder';
            }

            $assignedCoachesCount = $batch->coachAssignments->unique('coach_id')->count();

            return [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number ?: $batch->name,
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
                'year' => $startDate ? $startDate->year : null,
                'month' => $month,
                'day' => $startDate ? $startDate->day : null,
                'day_of_week' => $startDate ? $startDate->dayOfWeek : null,
                'day_name' => $startDate ? $startDate->format('l') : null,
                'is_weekend' => $startDate ? ($startDate->isWeekend() ? 1 : 0) : 1,
                'season_period' => $seasonPeriod,
                'total_participants' => $totalParticipants,
                'total_revenue_php' => $totalRevenue > 0 ? $totalRevenue : $collectedRevenue,
                'collected_revenue_php' => $collectedRevenue,
                'active_bookings_count' => $validBookings->count(),
                'class_distribution' => $classBreakdown,
                'pickup_distribution' => $pickupBreakdown,
                'assigned_coaches_count' => $assignedCoachesCount,
                'computed_capacity' => $batch->computed_capacity,
                'occupancy_percentage' => $batch->occupancy_percentage,
                'status' => $batch->status,
                'completed_at' => $batch->completed_at ? Carbon::parse($batch->completed_at)->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'exported_at' => Carbon::now()->toDateTimeString(),
            'total_records' => $trainingData->count(),
            'data' => $trainingData,
        ]);
    }

    /**
     * Ingest and store 90-day demand & revenue predictions from the ML pipeline.
     * POST /api/v1/ml/sync-forecast
     */
    public function importForecast(Request $request): JsonResponse
    {
        // Support either raw array of forecasts or structured object with horizon summaries
        $rawPayload = $request->all();

        $forecastList = [];
        $horizonSummaries = null;
        $metadata = [];

        if (isset($rawPayload['forecasts']) && is_array($rawPayload['forecasts'])) {
            $forecastList = $rawPayload['forecasts'];
            $horizonSummaries = $rawPayload['horizon_summaries'] ?? null;
            $metadata = $rawPayload['metadata'] ?? [];
        } elseif (isset($rawPayload['data']) && is_array($rawPayload['data'])) {
            $forecastList = $rawPayload['data'];
            $horizonSummaries = $rawPayload['horizon_summaries'] ?? null;
        } elseif (is_array($rawPayload) && !empty($rawPayload) && isset($rawPayload[0])) {
            $forecastList = $rawPayload;
        }

        $monthlyClassifications = $rawPayload['monthly_classifications']
            ?? $metadata['monthly_classifications']
            ?? $metadata['statistical_interpretation']['monthly_classifications']
            ?? null;
        if (!empty($monthlyClassifications)) {
            $metadata['monthly_classifications'] = $monthlyClassifications;
        }

        $monthlyForecasts = $rawPayload['monthly_forecasts']
            ?? $metadata['monthly_forecasts']
            ?? null;
        if (!empty($monthlyForecasts)) {
            $metadata['monthly_forecasts'] = $monthlyForecasts;
        }

        // Support CSV raw text upload if provided
        if (empty($forecastList) && $request->has('csv_data')) {
            $forecastList = $this->parseCsvForecast($request->input('csv_data'));
        } elseif ($request->hasFile('file')) {
            $csvContent = file_get_contents($request->file('file')->getRealPath());
            $forecastList = $this->parseCsvForecast($csvContent);
        }

        if (empty($forecastList)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No forecast records provided in request payload.',
            ], 422);
        }

        $now = Carbon::now();
        $recordsToInsert = [];

        foreach ($forecastList as $item) {
            if (empty($item['forecast_date'])) {
                continue;
            }

            $forecastDate = Carbon::parse($item['forecast_date'])->toDateString();
            $daysAhead = (int) ($item['days_ahead'] ?? Carbon::today()->diffInDays(Carbon::parse($forecastDate)));
            $predictedParticipants = (float) ($item['predicted_participants'] ?? 0);
            $predictedBookings = (float) ($item['predicted_bookings'] ?? 0);
            $predictedRevenue = (float) ($item['predicted_revenue_php'] ?? $item['predicted_revenue'] ?? 0);
            $demandLevel = (string) ($item['demand_level'] ?? ($predictedParticipants > 30 ? 'High' : ($predictedParticipants <= 18 ? 'Low' : 'Medium')));
            $seasonPeriod = (string) ($item['season_period'] ?? 'Off-Peak');
            $instructorsNeeded = (int) ($item['instructors_needed'] ?? (int) ceil($predictedParticipants / 4));

            $recordsToInsert[] = [
                'forecast_date' => $forecastDate,
                'days_ahead' => $daysAhead,
                'predicted_participants' => $predictedParticipants,
                'predicted_bookings' => $predictedBookings,
                'predicted_revenue_php' => $predictedRevenue,
                'demand_level' => ucfirst($demandLevel),
                'season_period' => ucfirst($seasonPeriod),
                'instructors_needed' => $instructorsNeeded,
                'horizon_summary' => $horizonSummaries ? json_encode($horizonSummaries) : null,
                'metadata' => !empty($metadata) ? json_encode($metadata) : null,
                'synced_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($recordsToInsert)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to parse any valid forecast rows from payload.',
            ], 422);
        }

        DB::transaction(function () use ($recordsToInsert) {
            // Replace existing forecast records with fresh sync run
            DemandForecast::query()->delete();
            DemandForecast::insert($recordsToInsert);
        });

        // Invalidate cached forecast so UI immediately updates
        \Illuminate\Support\Facades\Cache::forget('ml_demand_forecast');

        Log::info('ML Forecast successfully synced', [
            'count' => count($recordsToInsert),
            'first_date' => $recordsToInsert[0]['forecast_date'] ?? null,
            'last_date' => end($recordsToInsert)['forecast_date'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully synced ' . count($recordsToInsert) . ' demand forecast records.',
            'synced_count' => count($recordsToInsert),
            'first_forecast_date' => $recordsToInsert[0]['forecast_date'] ?? null,
            'last_forecast_date' => end($recordsToInsert)['forecast_date'] ?? null,
            'horizon_summaries' => $horizonSummaries,
            'monthly_classifications' => $monthlyClassifications,
            'monthly_forecasts' => $monthlyForecasts,
            'synced_at' => $now->toDateTimeString(),
        ]);
    }

    /**
     * Retrieve latest demand forecast data including monthly classifications.
     * GET /api/v1/ml/forecast
     */
    public function getForecast(Request $request): JsonResponse
    {
        $forecastService = app(\App\Services\DemandForecastService::class);
        $data = $forecastService->getForecastData();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Helper to parse CSV forecast content.
     */
    protected function parseCsvForecast(string $csvContent): array
    {
        $lines = explode("\n", trim($csvContent));
        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('trim', $headers);
        $rows = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            $cols = str_getcsv($line);
            if (count($cols) >= count($headers)) {
                $row = array_combine($headers, array_slice($cols, 0, count($headers)));
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
