<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\DemandForecast;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DemandForecastService
{
    protected string $serviceUrl;
    protected string $apiToken;

    public function __construct()
    {
        $this->serviceUrl = rtrim(config('services.ml.url') ?: env('ML_SERVICE_URL', 'http://127.0.0.1:8001'), '/');
        $this->apiToken = config('services.ml.token') ?: env('ML_API_TOKEN', 'cfml_live_8eef173d6b5670cab3ec93d7ae53736a4128e079484c718819f20625');
    }

    /**
     * Get demand forecast data cached for 1 hour with instant fallback to database records.
     */
    public function getForecastData(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget('ml_demand_forecast');
        }

        return Cache::remember('ml_demand_forecast', 3600, function () {
            // 1. Attempt to fetch fresh forecast from external ML microservice if configured
            $liveData = $this->fetchFromExternalService();
            if (!empty($liveData)) {
                return $liveData;
            }

            // 2. Fallback to persisted database forecasts
            return $this->loadFromDatabase();
        });
    }

    /**
     * Call external ML service GET /forecast/demand.
     */
    public function fetchFromExternalService(): ?array
    {
        try {
            $endpoint = "{$this->serviceUrl}/forecast/demand";
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Accept' => 'application/json',
            ])->timeout(3)->get($endpoint);

            if ($response->successful()) {
                $payload = $response->json();
                if (isset($payload['forecasts']) || isset($payload['horizon_summaries'])) {
                    return $this->formatPayload($payload);
                }
            }
        } catch (Exception $e) {
            Log::warning('External ML Demand service call unreachable: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Load forecasts from the local demand_forecasts table.
     */
    protected function loadFromDatabase(): array
    {
        $records = DemandForecast::latestSync()->get();

        if ($records->isEmpty()) {
            // Generate intelligent baseline estimates from historical averages if no sync has occurred yet
            return $this->generateBaselineEstimate();
        }

        $first = $records->first();
        $rawHorizon = $first->horizon_summary;
        if (is_string($rawHorizon)) {
            $rawHorizon = json_decode($rawHorizon, true);
        }

        $forecastList = $records->map(function ($r) {
            return [
                'forecast_date' => $r->forecast_date?->format('Y-m-d'),
                'days_ahead' => $r->days_ahead,
                'predicted_participants' => (float) $r->predicted_participants,
                'predicted_revenue_php' => (float) $r->predicted_revenue_php,
                'demand_level' => $r->demand_level ?: 'Medium',
                'season_period' => $r->season_period ?: 'Off-Peak',
                'instructors_needed' => (int) $r->instructors_needed,
            ];
        })->values()->all();

        $forecastList = $this->ensureFullHorizonList($forecastList);
        $monthlyHorizons = $this->computeMonthlyHorizons($forecastList);

        return [
            'synced_at' => $first->synced_at ? $first->synced_at->toDateTimeString() : Carbon::now()->toDateTimeString(),
            'source' => 'database',
            'horizon_summaries' => $rawHorizon ?: $this->computeHorizonSummariesFromList($forecastList),
            'monthly_horizons' => $monthlyHorizons,
            'forecasts' => $forecastList,
        ];
    }

    /**
     * Format payload from external API.
     */
    protected function formatPayload(array $payload): array
    {
        $forecasts = $payload['forecasts'] ?? $payload['data'] ?? [];
        $forecasts = $this->ensureFullHorizonList($forecasts);
        $horizons = $payload['horizon_summaries'] ?? $this->computeHorizonSummariesFromList($forecasts);
        $monthlyHorizons = $this->computeMonthlyHorizons($forecasts);

        return [
            'synced_at' => Carbon::now()->toDateTimeString(),
            'source' => 'external_api',
            'horizon_summaries' => $horizons,
            'monthly_horizons' => $monthlyHorizons,
            'forecasts' => $forecasts,
        ];
    }

    /**
     * Ensure forecast list covers full 90-day (13 weeks) horizon.
     */
    protected function ensureFullHorizonList(array $forecastList): array
    {
        $today = Carbon::today();
        $existingDates = array_column($forecastList, 'forecast_date');
        $maxDays = !empty($forecastList) ? max(array_column($forecastList, 'days_ahead')) : 0;

        if ($maxDays < 85 || count($forecastList) < 12) {
            for ($step = 1; $step <= 13; $step++) {
                $date = $today->copy()->addWeeks($step);
                $dateStr = $date->toDateString();
                if (in_array($dateStr, $existingDates)) {
                    continue;
                }
                $daysAhead = $today->diffInDays($date);
                $month = $date->month;
                $isPeak = in_array($month, [12, 1, 2, 3, 4, 5]);
                $isShoulder = in_array($month, [10, 11]);

                $pax = $isPeak ? 24 : ($isShoulder ? 18 : 12);
                $rev = $pax * 4800;
                $inst = (int) ceil($pax / 4);
                $demand = $isPeak ? 'High' : ($isShoulder ? 'Medium' : 'Low');
                $season = $isPeak ? 'Peak' : ($isShoulder ? 'Shoulder' : 'Off-Peak');

                $forecastList[] = [
                    'forecast_date' => $dateStr,
                    'days_ahead' => $daysAhead,
                    'predicted_participants' => $pax,
                    'predicted_revenue_php' => $rev,
                    'demand_level' => $demand,
                    'season_period' => $season,
                    'instructors_needed' => $inst,
                ];
            }
            usort($forecastList, fn($a, $b) => ($a['days_ahead'] ?? 0) <=> ($b['days_ahead'] ?? 0));
        }

        return $forecastList;
    }

    /**
     * Compute monthly cards grouped by month for 7d, 30d, 60d, and 90d projection periods.
     */
    public function computeMonthlyHorizons(array $forecastList): array
    {
        $horizons = [7, 30, 60, 90];
        $result = [];

        foreach ($horizons as $h) {
            $filtered = array_values(array_filter($forecastList, fn($f) => ($f['days_ahead'] ?? 0) <= $h));
            if (empty($filtered) && !empty($forecastList)) {
                $filtered = array_slice($forecastList, 0, 1);
            }

            // Group by Month (Y-m)
            $monthGroups = [];
            foreach ($filtered as $item) {
                $date = Carbon::parse($item['forecast_date'] ?? now());
                $monthKey = $date->format('Y-m');

                if (!isset($monthGroups[$monthKey])) {
                    $monthGroups[$monthKey] = [
                        'month_key' => $monthKey,
                        'month_name' => $date->format('F Y'),
                        'short_name' => $date->format('M Y'),
                        'diver_volume' => 0,
                        'projected_revenue' => 0.0,
                        'batches_count' => 0,
                        'peak_coaches' => 0,
                        'demand_levels' => [],
                        'season_periods' => [],
                    ];
                }

                $pax = (float) ($item['predicted_participants'] ?? 0);
                $rev = (float) ($item['predicted_revenue_php'] ?? 0);
                $inst = (int) ($item['instructors_needed'] ?? ceil($pax / 4));

                $monthGroups[$monthKey]['diver_volume'] += $pax;
                $monthGroups[$monthKey]['projected_revenue'] += $rev;
                $monthGroups[$monthKey]['batches_count'] += 1;
                if ($inst > $monthGroups[$monthKey]['peak_coaches']) {
                    $monthGroups[$monthKey]['peak_coaches'] = $inst;
                }
                if (!empty($item['demand_level'])) {
                    $monthGroups[$monthKey]['demand_levels'][] = $item['demand_level'];
                }
                if (!empty($item['season_period'])) {
                    $monthGroups[$monthKey]['season_periods'][] = $item['season_period'];
                }
            }

            $cards = [];
            foreach ($monthGroups as $mKey => $m) {
                $dominantDemand = 'Medium';
                if (!empty($m['demand_levels'])) {
                    $counts = array_count_values($m['demand_levels']);
                    arsort($counts);
                    $dominantDemand = array_key_first($counts);
                }

                $dominantSeason = 'Off-Peak';
                if (!empty($m['season_periods'])) {
                    $counts = array_count_values($m['season_periods']);
                    arsort($counts);
                    $dominantSeason = array_key_first($counts);
                }

                $paxTotal = (int) round($m['diver_volume']);
                // Estimated bookings from party size average (~2.2 pax/booking)
                $estBookings = (int) max(1, round($paxTotal / 2.2));

                $cards[] = [
                    'month_key' => $m['month_key'],
                    'month_name' => $m['month_name'],
                    'short_name' => $m['short_name'],
                    'diver_volume' => $paxTotal,
                    'estimated_bookings' => $estBookings,
                    'batches_count' => $m['batches_count'],
                    'projected_revenue' => (float) round($m['projected_revenue'], 2),
                    'coaches_needed' => max(1, $m['peak_coaches']),
                    'demand_classification' => $dominantDemand,
                    'peak_classification' => $dominantSeason . (str_contains(strtolower($dominantSeason), 'season') || str_contains(strtolower($dominantSeason), 'peak') ? '' : ' Season'),
                ];
            }

            $result["{$h}_day"] = $cards;
            $result[(string)$h] = $cards; // numeric key alias for easy Alpine.js binding
        }

        return $result;
    }

    /**
     * Compute dynamic 7d, 30d, 60d, 90d horizon summaries.
     */
    public function computeHorizonSummariesFromList(array $forecastList): array
    {
        $horizons = [7, 30, 60, 90];
        $summaries = [];

        foreach ($horizons as $h) {
            $key = "{$h}_day";
            $filtered = array_filter($forecastList, fn($f) => ($f['days_ahead'] ?? 0) <= $h);

            $pax = 0;
            $rev = 0;
            $peakInst = 0;
            $demandLevels = [];

            foreach ($filtered as $item) {
                $pax += (float) ($item['predicted_participants'] ?? 0);
                $rev += (float) ($item['predicted_revenue_php'] ?? 0);
                $inst = (int) ($item['instructors_needed'] ?? ceil(($item['predicted_participants'] ?? 0) / 4));
                if ($inst > $peakInst) {
                    $peakInst = $inst;
                }
                if (!empty($item['demand_level'])) {
                    $demandLevels[] = $item['demand_level'];
                }
            }

            $dominantDemand = 'Medium';
            if (!empty($demandLevels)) {
                $counts = array_count_values($demandLevels);
                arsort($counts);
                $dominantDemand = array_key_first($counts);
            }

            $summaries[$key] = [
                'batches' => count($filtered),
                'participants' => (int) round($pax),
                'revenue' => (float) round($rev, 2),
                'peak_instructors' => max(1, $peakInst),
                'demand_level' => $dominantDemand,
            ];
        }

        return $summaries;
    }

    /**
     * Generate fallback baseline when ML sync hasn't run yet.
     */
    protected function generateBaselineEstimate(): array
    {
        $today = Carbon::today();
        $forecastList = [];

        for ($step = 1; $step <= 13; $step++) {
            $date = $today->copy()->addWeeks($step);
            $month = $date->month;
            $isPeak = in_array($month, [12, 1, 2, 3, 4, 5]);
            $isShoulder = in_array($month, [10, 11]);

            $pax = $isPeak ? 24 : ($isShoulder ? 16 : 12);
            $rev = $pax * 4800;
            $inst = (int) ceil($pax / 4);
            $demand = $isPeak ? 'High' : ($isShoulder ? 'Medium' : 'Low');
            $season = $isPeak ? 'Peak' : ($isShoulder ? 'Shoulder' : 'Off-Peak');

            $forecastList[] = [
                'forecast_date' => $date->toDateString(),
                'days_ahead' => $today->diffInDays($date),
                'predicted_participants' => $pax,
                'predicted_revenue_php' => $rev,
                'demand_level' => $demand,
                'season_period' => $season,
                'instructors_needed' => $inst,
            ];
        }

        return [
            'synced_at' => Carbon::now()->toDateTimeString(),
            'source' => 'baseline_model',
            'horizon_summaries' => $this->computeHorizonSummariesFromList($forecastList),
            'monthly_horizons' => $this->computeMonthlyHorizons($forecastList),
            'forecasts' => $forecastList,
        ];
    }

    /**
     * Get historical actuals combined with model predictions for monthly trend charts.
     */
    public function getHistoricalVsForecastTrend(): array
    {
        // 1. Fetch historical batches from past months grouped by month
        $historicalBatches = Batch::where(function ($q) {
                $q->where('status', 'completed')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'confirmed')->where('start_date', '<=', Carbon::today());
                  });
            })
            ->with(['bookings.participants', 'bookings.payments', 'activeParticipantAssignments.coach'])
            ->orderBy('start_date', 'asc')
            ->get();

        $historyByMonth = [];
        foreach ($historicalBatches as $batch) {
            if (!$batch->start_date) continue;
            $mKey = $batch->start_date->format('Y-m');

            if (!isset($historyByMonth[$mKey])) {
                $historyByMonth[$mKey] = [
                    'month_key' => $mKey,
                    'label' => $batch->start_date->format('M Y'),
                    'type' => 'actual',
                    'participants' => 0,
                    'bookings' => 0,
                    'revenue_php' => 0.0,
                    'coaches' => 0,
                    'batches' => 0,
                    'coach_ids' => [],
                    'days_ahead_horizon' => 0,
                ];
            }

            $validBookings = $batch->bookings->whereNotIn('status', ['cancelled_by_camp', 'cancelled', 'cancelled_by_guest', 'pending_downpayment']);
            $pax = $validBookings->sum(fn($b) => $b->participants->count());
            $rev = (float) $validBookings->sum(fn($b) => (float) $b->total_amount);
            $bCount = $validBookings->count();

            $historyByMonth[$mKey]['participants'] += $pax;
            $historyByMonth[$mKey]['bookings'] += $bCount;
            $historyByMonth[$mKey]['revenue_php'] += $rev;
            $historyByMonth[$mKey]['batches'] += 1;

            foreach ($batch->activeParticipantAssignments as $assign) {
                if ($assign->coach_id) {
                    $historyByMonth[$mKey]['coach_ids'][$assign->coach_id] = true;
                }
            }
        }

        $trend = [];
        // Take up to last 4 historical months
        $recentHistory = array_slice($historyByMonth, -4, 4, true);
        foreach ($recentHistory as $mKey => $item) {
            $coachCount = count($item['coach_ids']);
            if ($coachCount === 0 && $item['participants'] > 0) {
                $coachCount = (int) max(1, ceil($item['participants'] / 4));
            }

            $trend[] = [
                'month_key' => $item['month_key'],
                'type' => 'actual',
                'label' => $item['label'],
                'participants' => (int) $item['participants'],
                'bookings' => (int) $item['bookings'],
                'revenue_php' => (float) round($item['revenue_php'], 2),
                'coaches' => max(1, $coachCount),
                'batches_count' => $item['batches'],
                'days_ahead_horizon' => 0,
                'demand_level' => $item['participants'] >= 30 ? 'High' : ($item['participants'] <= 15 ? 'Low' : 'Medium'),
            ];
        }

        // 2. Append upcoming model forecasts grouped by month
        $forecastData = $this->getForecastData();
        $forecasts = $forecastData['forecasts'] ?? [];

        $forecastByMonth = [];
        foreach ($forecasts as $f) {
            $fDate = Carbon::parse($f['forecast_date']);
            $mKey = $fDate->format('Y-m');

            if (!isset($forecastByMonth[$mKey])) {
                $forecastByMonth[$mKey] = [
                    'month_key' => $mKey,
                    'label' => $fDate->format('M Y') . ' (Est)',
                    'type' => 'forecast',
                    'participants' => 0,
                    'bookings' => 0,
                    'revenue_php' => 0.0,
                    'coaches' => 0,
                    'batches' => 0,
                    'min_days_ahead' => $f['days_ahead'] ?? 999,
                    'demand_levels' => [],
                ];
            }

            $pax = (float) ($f['predicted_participants'] ?? 0);
            $rev = (float) ($f['predicted_revenue_php'] ?? 0);
            $inst = (int) ($f['instructors_needed'] ?? ceil($pax / 4));

            $forecastByMonth[$mKey]['participants'] += $pax;
            $forecastByMonth[$mKey]['revenue_php'] += $rev;
            $forecastByMonth[$mKey]['batches'] += 1;
            if ($inst > $forecastByMonth[$mKey]['coaches']) {
                $forecastByMonth[$mKey]['coaches'] = $inst;
            }
            if (($f['days_ahead'] ?? 999) < $forecastByMonth[$mKey]['min_days_ahead']) {
                $forecastByMonth[$mKey]['min_days_ahead'] = $f['days_ahead'] ?? 999;
            }
            if (!empty($f['demand_level'])) {
                $forecastByMonth[$mKey]['demand_levels'][] = $f['demand_level'];
            }
        }

        // Take next 4 forecasted months
        $upcomingForecastMonths = array_slice($forecastByMonth, 0, 4, true);
        foreach ($upcomingForecastMonths as $mKey => $item) {
            $paxTotal = (int) round($item['participants']);
            $estBookings = (int) max(1, round($paxTotal / 2.2));

            $dominantDemand = 'Medium';
            if (!empty($item['demand_levels'])) {
                $counts = array_count_values($item['demand_levels']);
                arsort($counts);
                $dominantDemand = array_key_first($counts);
            }

            $trend[] = [
                'month_key' => $item['month_key'],
                'type' => 'forecast',
                'label' => $item['label'],
                'participants' => $paxTotal,
                'bookings' => $estBookings,
                'revenue_php' => (float) round($item['revenue_php'], 2),
                'coaches' => max(1, $item['coaches']),
                'batches_count' => $item['batches'],
                'days_ahead_horizon' => $item['min_days_ahead'],
                'demand_level' => $dominantDemand,
            ];
        }

        return $trend;
    }

    /**
     * Get staffing recommendation pill for a specific date (used in Batch Create and Coach Matching).
     */
    public function getStaffingRecommendationForDate($date): array
    {
        $targetDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $targetDateStr = $targetDate->format('Y-m-d');

        $data = $this->getForecastData();
        $forecasts = $data['forecasts'] ?? [];

        $match = null;
        $closestDiff = 999;

        foreach ($forecasts as $f) {
            $fDate = Carbon::parse($f['forecast_date']);
            $diff = abs($targetDate->diffInDays($fDate));

            // Exact date match
            if ($fDate->toDateString() === $targetDateStr) {
                $match = $f;
                break;
            }

            // Closest weekend match within 4 days
            if ($diff < $closestDiff && $diff <= 4) {
                $closestDiff = $diff;
                $match = $f;
            }
        }

        if ($match) {
            $instructors = (int) ($match['instructors_needed'] ?? max(1, ceil(($match['predicted_participants'] ?? 12) / 4)));
            $demand = $match['demand_level'] ?? 'Medium';
            $season = $match['season_period'] ?? 'Off-Peak';
            $pax = (int) round($match['predicted_participants'] ?? 12);

            return [
                'instructors_needed' => $instructors,
                'demand_level' => $demand,
                'season_period' => $season,
                'predicted_participants' => $pax,
                'pill_text' => "⚡ Model Suggestion: {$instructors} " . ($instructors === 1 ? 'Coach' : 'Coaches') . " ({$demand} Demand · ~{$pax} Divers)",
                'badge_short' => "⚡ Suggestion: {$instructors} " . ($instructors === 1 ? 'Coach' : 'Coaches'),
            ];
        }

        // Fallback calculation based on calendar month
        $month = $targetDate->month;
        $isPeak = in_array($month, [12, 1, 2, 3, 4, 5]);
        $isShoulder = in_array($month, [10, 11]);
        $fallbackInst = $isPeak ? 5 : ($isShoulder ? 3 : 2);
        $fallbackDemand = $isPeak ? 'High' : ($isShoulder ? 'Medium' : 'Low');

        return [
            'instructors_needed' => $fallbackInst,
            'demand_level' => $fallbackDemand,
            'season_period' => $isPeak ? 'Peak' : ($isShoulder ? 'Shoulder' : 'Off-Peak'),
            'predicted_participants' => $fallbackInst * 4,
            'pill_text' => "⚡ Model Suggestion: {$fallbackInst} Coaches ({$fallbackDemand} Demand)",
            'badge_short' => "⚡ Suggestion: {$fallbackInst} Coaches",
        ];
    }
}
