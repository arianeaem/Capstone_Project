<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\WeatherForecastService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class WeatherSafetyController extends Controller
{
    public function __construct(
        protected WeatherForecastService $forecastService
    ) {}

    /**
     * Page 1: Weather & Safety Monitoring Batch Roster.
     */
    public function index(Request $request): View
    {
        $query = Batch::with([
            'bookings',
            'riskAssessments' => fn($q) => $q->orderBy('assessed_at', 'desc'),
            'manualOverrides',
        ]);

        // Filter: Risk Classification
        if ($request->filled('risk')) {
            $query->where('risk_classification', $request->input('risk'));
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter: Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->input('date_to'));
        }

        // Critical and High Risk count
        $criticalCount = Batch::whereIn('risk_classification', ['high_risk', 'critical_risk'])->count();

        // Paginate by soonest dive date
        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));
        $batches = $query->orderBy('start_date', 'asc')->paginate($perPage)->withQueryString();

        // 24-Hour Master Continuous Cache Info
        $lastUpdatedAt = Cache::get('forecast:last_updated_at');
        $masterForecast = Cache::get('forecast:continuous_16d');
        if (!$masterForecast) {
            try {
                $masterForecast = $this->forecastService->updateAllForecasts(16);
                $lastUpdatedAt = Cache::get('forecast:last_updated_at');
            } catch (\Throwable $e) {
                // Ignore API failure
            }
        }

        return view('admin.weather.index', compact('batches', 'criticalCount', 'lastUpdatedAt', 'masterForecast'));
    }

    /**
     * One-Click Operator Sync 16-Day 24-Hour Forecast Cache.
     */
    public function syncCache(): RedirectResponse
    {
        try {
            $result = $this->forecastService->updateAllForecasts(16);
            return back()->with('success', "Successfully synced 24-hour continuous weather & marine forecast cache for the next {$result['days_cached']} days from Open-Meteo.");
        } catch (Exception $e) {
            return back()->with('error', "Forecast cache sync failed: " . $e->getMessage());
        }
    }


    /**
     * Page 2: Deep-Dive Batch Weather Dashboard.
     */
    public function show(Batch $batch): View
    {
        $batch->load([
            'bookings.participants',
            'riskAssessments.hourlyAssessments',
            'manualOverrides.operator',
            'notificationLogs',
        ]);

        $day1Assessment = $batch->latestDay1Assessment;
        $day2Assessment = $batch->latestDay2Assessment;
        $latestOverride = $batch->latestManualOverride;

        // Auto-run assessment if not yet assessed
        if (!$day1Assessment || !$day2Assessment) {
            $result = $this->forecastService->assessBatch($batch, null, auth()->user());
            $batch->refresh();
            $day1Assessment = $batch->latestDay1Assessment;
            $day2Assessment = $batch->latestDay2Assessment;
        }

        // Overall classification: worse of Day 1 and Day 2
        $overallClassification = 'Safe';
        if ($day1Assessment && $day2Assessment) {
            $rank1 = WeatherForecastService::RISK_RANK[$day1Assessment->overall_classification] ?? 1;
            $rank2 = WeatherForecastService::RISK_RANK[$day2Assessment->overall_classification] ?? 1;
            $worseRank = max($rank1, $rank2);
            $overallClassification = array_search($worseRank, WeatherForecastService::RISK_RANK) ?: 'Safe';
        }

        // Group past assessment runs (pair Day 1 and Day 2 assessments from the same assessment run)
        $allAssessments = $batch->riskAssessments()
            ->with(['assessor'])
            ->orderBy('id', 'desc')
            ->get();

        $runs = [];
        $visited = [];
        foreach ($allAssessments as $item) {
            if (isset($visited[$item->id])) {
                continue;
            }

            $runGroup = collect([$item]);
            $visited[$item->id] = true;

            // Look for paired assessment for the opposite day number from the same run (within 3 minutes)
            $otherDay = ((int) $item->day_number === 1) ? 2 : 1;
            $pair = $allAssessments->first(function ($candidate) use ($item, $otherDay, $visited) {
                if (isset($visited[$candidate->id])) {
                    return false;
                }
                if ((int) $candidate->day_number !== $otherDay) {
                    return false;
                }
                if (!$item->assessed_at || !$candidate->assessed_at) {
                    return false;
                }
                return abs($item->assessed_at->diffInSeconds($candidate->assessed_at)) <= 180;
            });

            if ($pair) {
                $runGroup->push($pair);
                $visited[$pair->id] = true;
            }

            $timestampKey = $item->assessed_at ? $item->assessed_at->format('Y-m-d H:i:s') : 'run_' . $item->id;
            $runs[$timestampKey] = $runGroup;
        }

        $assessmentRuns = collect($runs);

        // Fetch 24-Hour Continuous Profiles for Day 1 and Day 2
        $day1Date = $batch->start_date->format('Y-m-d');
        $day2Date = $batch->end_date ? $batch->end_date->format('Y-m-d') : $batch->start_date->copy()->addDay()->format('Y-m-d');
        $day1Continuous24h = Cache::get("forecast:date:{$day1Date}");
        $day2Continuous24h = Cache::get("forecast:date:{$day2Date}");

        // Auto-refresh continuous 16-day cache on demand if missing or expired (e.g. IDE/server restarted)
        if ((!$day1Continuous24h || empty($day1Continuous24h['hourly'])) && Carbon::now()->diffInDays($batch->start_date, false) <= 16) {
            try {
                $master = $this->forecastService->updateAllForecasts(16);
                $day1Continuous24h = $master['daily_summaries'][$day1Date] ?? Cache::get("forecast:date:{$day1Date}");
                $day2Continuous24h = $master['daily_summaries'][$day2Date] ?? Cache::get("forecast:date:{$day2Date}");
            } catch (\Throwable $e) {
                // If Open-Meteo API is temporarily unreachable, gracefully fallback to DB
            }
        }

        // Resilient Fallback: If 24h continuous cache is still empty, populate from persisted DB hourly assessments
        if (empty($day1Continuous24h['hourly']) && $day1Assessment && $day1Assessment->hourlyAssessments->isNotEmpty()) {
            $day1Continuous24h = [
                'date' => $day1Date,
                'hourly' => $day1Assessment->hourlyAssessments->map(fn($h) => [
                    'hour' => (int) $h->forecast_time->format('H'),
                    'time' => $h->forecast_time->format('H:i'),
                    'classification' => $h->classification,
                    'wave_height' => (float) $h->wave_height,
                    'wave_period' => (float) $h->wave_period,
                    'swell_height' => (float) $h->swell_height,
                    'ocean_current' => (float) $h->ocean_current,
                    'wind_wave_height' => (float) $h->wind_wave_height,
                    'rain' => (float) $h->rain,
                    'sea_level_pressure' => (float) $h->sea_level_pressure,
                    'wind_speed' => (float) $h->wind_speed,
                    'wind_direction' => (float) $h->wind_direction,
                ])->toArray(),
            ];
        }

        if (empty($day2Continuous24h['hourly']) && $day2Assessment && $day2Assessment->hourlyAssessments->isNotEmpty()) {
            $day2Continuous24h = [
                'date' => $day2Date,
                'hourly' => $day2Assessment->hourlyAssessments->map(fn($h) => [
                    'hour' => (int) $h->forecast_time->format('H'),
                    'time' => $h->forecast_time->format('H:i'),
                    'classification' => $h->classification,
                    'wave_height' => (float) $h->wave_height,
                    'wave_period' => (float) $h->wave_period,
                    'swell_height' => (float) $h->swell_height,
                    'ocean_current' => (float) $h->ocean_current,
                    'wind_wave_height' => (float) $h->wind_wave_height,
                    'rain' => (float) $h->rain,
                    'sea_level_pressure' => (float) $h->sea_level_pressure,
                    'wind_speed' => (float) $h->wind_speed,
                    'wind_direction' => (float) $h->wind_direction,
                ])->toArray(),
            ];
        }

        return view('admin.weather.show', compact(
            'batch',
            'day1Assessment',
            'day2Assessment',
            'latestOverride',
            'overallClassification',
            'assessmentRuns',
            'day1Continuous24h',
            'day2Continuous24h'
        ));
    }

    /**
     * One-Click Operator Run Assessment.
     */
    public function assess(Batch $batch): RedirectResponse
    {
        try {
            $this->forecastService->assessBatch($batch, null, auth()->user());
            return back()->with('success', "✓ Weather risk assessed across all 4 fixed windows for batch {$batch->batch_code}.");
        } catch (Exception $e) {
            return back()->with('error', "Assessment failed: " . $e->getMessage());
        }
    }

    /**
     * Submit Manual Override (PAGASA-style advisories).
     */
    public function override(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'tcws_signal' => 'nullable|integer|min:0|max:5',
            'gale_warning' => 'nullable|boolean',
            'thunderstorm_advisory' => 'nullable|boolean',
            'typhoon_within_distance' => 'nullable|boolean',
            'tsunami_warning' => 'nullable|boolean',
            'reason' => 'required|string|max:1000',
            'cancel_batch' => 'nullable|boolean',
        ]);

        try {
            $cancelBatch = $request->boolean('cancel_batch');
            $this->forecastService->applyManualOverride(
                $batch,
                $validated,
                auth()->user(),
                $cancelBatch,
                $validated['reason']
            );

            $msg = "✓ Manual override applied to batch {$batch->batch_code}. Both days forced to Critical Risk.";
            if ($cancelBatch) {
                $msg .= " Batch cancelled, 100% force majeure refund eligibility triggered, and customer cancellation notifications dispatched.";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', "Override failed: " . $e->getMessage());
        }
    }

    /**
     * Confirm Whole-Batch Cancellation from Risk Assessment or Override.
     */
    public function cancel(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            $sentCount = $this->forecastService->cancelBatchWithRefundsAndNotifications(
                $batch,
                $validated['cancellation_reason'],
                auth()->user()
            );

            return back()->with('success', "✓ Batch {$batch->batch_code} cancelled. {$sentCount} cancellation notification email(s) dispatched to affected bookings.");
        } catch (Exception $e) {
            return back()->with('error', "Cancellation failed: " . $e->getMessage());
        }
    }
}
