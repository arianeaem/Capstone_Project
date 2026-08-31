<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\CoachAvailability;
use App\Models\CoachOpening;
use App\Models\CoachRequest;
use App\Models\ParticipantAssignment;
use App\Services\WeatherForecastService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(
        protected WeatherForecastService $forecastService
    ) {}

    /**
     * Page 1: Coach Portal Dashboard.
     */
    public function index(): View
    {
        $coach = Auth::user();
        $today = Carbon::today();

        // 1. Next Upcoming Assignment
        $nextAssignment = ParticipantAssignment::with(['batch.riskAssessments', 'participant', 'booking'])
            ->where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereDate('dive_date', '>=', $today)
            ->orderBy('dive_date', 'asc')
            ->first();

        $nextSessionData = null;
        if ($nextAssignment && $nextAssignment->batch) {
            $batch = $nextAssignment->batch;
            $assignedParticipants = ParticipantAssignment::with(['participant', 'booking'])
                ->where('coach_id', $coach->id)
                ->where('batch_id', $batch->id)
                ->where('status', 'assigned')
                ->get()
                ->pluck('participant')
                ->unique('id');

            // Class breakdown
            $classBreakdown = [];
            foreach ($assignedParticipants as $p) {
                $type = $p->booking?->formatted_class_type ?? 'Freediving Class';
                $classBreakdown[$type] = ($classBreakdown[$type] ?? 0) + 1;
            }

            // Weather classification for this batch
            $d1Assessment = $batch->latestDay1Assessment;
            $weatherClass = $d1Assessment ? $d1Assessment->overall_classification : 'Safe';
            $weatherBadge = $d1Assessment ? $d1Assessment->classification_badge : [
                'label' => 'Safe',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];

            // Check if within 48 hours of dive date (09:30 AM start)
            $diveStart = $batch->start_date->copy()->setTime(9, 30);
            $hoursUntilDive = max(0, Carbon::now()->diffInHours($diveStart, false));
            $canRequestRelease = $hoursUntilDive > 48;

            $nextSessionData = [
                'batch' => $batch,
                'dive_date' => $nextAssignment->dive_date,
                'students' => $assignedParticipants,
                'students_count' => $assignedParticipants->count(),
                'class_breakdown' => $classBreakdown,
                'weather_class' => $weatherClass,
                'weather_badge' => $weatherBadge,
                'hours_until_dive' => $hoursUntilDive,
                'can_request_release' => $canRequestRelease,
            ];
        }

        // 2. Metrics & KPI Counts
        $availableDaysCount = CoachAvailability::where('coach_id', $coach->id)
            ->where('status', 'available')
            ->whereDate('date', '>=', $today)
            ->count();

        $pendingRequestsCount = CoachRequest::where('coach_id', $coach->id)
            ->where('status', 'pending')
            ->count();

        $upcomingConfirmedDivesCount = ParticipantAssignment::where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereDate('dive_date', '>=', $today)
            ->distinct('batch_id')
            ->count('batch_id');

        $activeOpeningsCount = CoachOpening::where('status', 'open')
            ->whereDate('dive_date', '>=', $today)
            ->count();

        // 3. Upcoming Schedule Preview (Next 3 upcoming batches)
        $upcomingAssignments = ParticipantAssignment::with(['batch.riskAssessments', 'participant', 'booking'])
            ->where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereDate('dive_date', '>=', $today)
            ->orderBy('dive_date', 'asc')
            ->get()
            ->groupBy('batch_id')
            ->take(3);

        return view('coach.dashboard', compact(
            'coach',
            'nextSessionData',
            'availableDaysCount',
            'pendingRequestsCount',
            'upcomingConfirmedDivesCount',
            'activeOpeningsCount',
            'upcomingAssignments'
        ));
    }
}
