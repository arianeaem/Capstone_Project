<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\AssignmentReleaseRequest;
use App\Models\Batch;
use App\Models\CoachAvailability;
use App\Models\ParticipantAssignment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * Page 2: Coach Availability Calendar.
     */
    public function index(Request $request): View
    {
        $coach = Auth::user();

        // Month Navigation (Default to current month or requested year/month)
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);

        $currentMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $startOfCalendar = $currentMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        // Fetch all availabilities for this coach within calendar window
        $availabilities = CoachAvailability::where('coach_id', $coach->id)
            ->whereBetween('date', [$startOfCalendar->format('Y-m-d'), $endOfCalendar->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        // Fetch all assigned dates for this coach
        $assignments = ParticipantAssignment::with('batch')
            ->where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereBetween('dive_date', [$startOfCalendar->format('Y-m-d'), $endOfCalendar->format('Y-m-d')])
            ->get()
            ->groupBy(fn($a) => $a->dive_date->format('Y-m-d'));

        // Fetch active release requests
        $releaseRequests = AssignmentReleaseRequest::where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->keyBy(fn($r) => $r->dive_date->format('Y-m-d'));

        // Build calendar grid days
        $calendarDays = [];
        $dayCursor = $startOfCalendar->copy();

        while ($dayCursor->lte($endOfCalendar)) {
            $dateStr = $dayCursor->format('Y-m-d');
            $isAssigned = $assignments->has($dateStr);
            $assignmentList = $assignments->get($dateStr, collect());
            $firstAssignment = $assignmentList->first();
            $batch = $firstAssignment?->batch;

            $hasReleaseRequest = $releaseRequests->has($dateStr);
            $releaseRequest = $releaseRequests->get($dateStr);

            $status = 'unset';
            if ($isAssigned) {
                $status = 'assigned';
            } elseif ($availabilities->has($dateStr)) {
                $status = $availabilities->get($dateStr)->status;
            }

            // Determine if $>48 hours away for emergency release request
            $diveStart = $dayCursor->copy()->setTime(9, 30);
            $hoursUntilDive = max(0, Carbon::now()->diffInHours($diveStart, false));
            $canRequestRelease = $isAssigned && ($hoursUntilDive > 48) && !$hasReleaseRequest;

            $calendarDays[] = [
                'date' => $dayCursor->copy(),
                'date_str' => $dateStr,
                'day_number' => (int) $dayCursor->format('j'),
                'is_current_month' => $dayCursor->month === $currentMonth->month,
                'is_today' => $dayCursor->isToday(),
                'is_past' => $dayCursor->isPast() && !$dayCursor->isToday(),
                'is_weekend' => $dayCursor->isWeekend(),
                'status' => $status,
                'is_assigned' => $isAssigned,
                'batch' => $batch,
                'students_count' => $assignmentList->count(),
                'has_release_request' => $hasReleaseRequest,
                'release_request' => $releaseRequest,
                'can_request_release' => $canRequestRelease,
                'hours_until_dive' => $hoursUntilDive,
            ];

            $dayCursor->addDay();
        }

        return view('coach.availability.index', compact(
            'coach',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'calendarDays',
            'year',
            'month'
        ));
    }

    /**
     * Toggle a single 2D1N date pair availability.
     */
    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $coach = Auth::user();
        $startDate = Carbon::parse($request->input('date'))->startOfDay();

        // Prevent modifying past dates
        if ($startDate->isPast() && !$startDate->isToday()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Cannot modify past dates.'], 422);
            }
            return back()->with('error', 'Cannot modify availability for past dates.');
        }

        // 2D1N Auto-Pairing: If Saturday -> Sunday, if Sunday -> Saturday, otherwise Day + (Day + 1)
        if ($startDate->isSaturday()) {
            $day1 = $startDate->copy();
            $day2 = $startDate->copy()->addDay();
        } elseif ($startDate->isSunday()) {
            $day1 = $startDate->copy()->subDay();
            $day2 = $startDate->copy();
        } else {
            $day1 = $startDate->copy();
            $day2 = $startDate->copy()->addDay();
        }

        $datesToUpdate = [$day1->format('Y-m-d'), $day2->format('Y-m-d')];

        // Check if either date is already assigned
        $hasAssignment = ParticipantAssignment::where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereIn('dive_date', $datesToUpdate)
            ->exists();

        if ($hasAssignment) {
            $msg = 'Assigned dates are locked from self-editing. Please submit an emergency release request if you cannot attend.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Check current status of day 1 to determine toggle target
        $currentRecord = CoachAvailability::where('coach_id', $coach->id)
            ->where('date', $day1->format('Y-m-d'))
            ->first();

        // If currently 'available' -> remove availability (unselect); otherwise -> set to 'available'
        if ($currentRecord && $currentRecord->status === 'available') {
            CoachAvailability::where('coach_id', $coach->id)
                ->whereIn('date', $datesToUpdate)
                ->delete();

            $newStatus = 'unset';
            $message = "Availability removed for {$day1->format('M d')} and {$day2->format('M d, Y')}.";
        } else {
            foreach ($datesToUpdate as $d) {
                CoachAvailability::updateOrCreate(
                    [
                        'coach_id' => $coach->id,
                        'date' => $d,
                    ],
                    [
                        'status' => 'available',
                        'notes' => 'Selected via Coach Portal availability calendar.',
                    ]
                );
            }

            $newStatus = 'available';
            $message = "Availability selected for {$day1->format('M d')} and {$day2->format('M d, Y')}.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus,
                'dates' => $datesToUpdate,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Bulk Edit Mode: Apply availability status across multiple selected date pairs.
     */
    public function bulkUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date',
            'status' => 'required|in:available,unavailable,remove',
        ]);

        $coach = Auth::user();
        $targetStatus = $request->input('status');
        $rawDates = $request->input('dates');

        $expandedDates = [];
        foreach ($rawDates as $dStr) {
            $d = Carbon::parse($dStr)->startOfDay();
            if ($d->isPast() && !$d->isToday()) {
                continue;
            }

            // 2D1N pairing
            if ($d->isSaturday()) {
                $expandedDates[] = $d->format('Y-m-d');
                $expandedDates[] = $d->copy()->addDay()->format('Y-m-d');
            } elseif ($d->isSunday()) {
                $expandedDates[] = $d->copy()->subDay()->format('Y-m-d');
                $expandedDates[] = $d->format('Y-m-d');
            } else {
                $expandedDates[] = $d->format('Y-m-d');
                $expandedDates[] = $d->copy()->addDay()->format('Y-m-d');
            }
        }

        $uniqueDates = array_values(array_unique($expandedDates));

        // Filter out assigned dates
        $assignedDates = ParticipantAssignment::where('coach_id', $coach->id)
            ->where('status', 'assigned')
            ->whereIn('dive_date', $uniqueDates)
            ->pluck('dive_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $updatedCount = 0;
        foreach ($uniqueDates as $d) {
            if (in_array($d, $assignedDates)) {
                continue; // Skip locked assigned dates
            }

            if ($targetStatus === 'available') {
                CoachAvailability::updateOrCreate(
                    [
                        'coach_id' => $coach->id,
                        'date' => $d,
                    ],
                    [
                        'status' => 'available',
                        'notes' => 'Updated via Coach Portal Bulk Edit.',
                    ]
                );
            } else {
                CoachAvailability::where('coach_id', $coach->id)
                    ->where('date', $d)
                    ->delete();
            }
            $updatedCount++;
        }

        $label = $targetStatus === 'available' ? 'Available' : 'Unavailable';
        $message = "Successfully updated {$updatedCount} days to {$label}. (Assigned dates remained locked).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Submit an Emergency Release Request for an assigned batch/date.
     * Enforces the 48-hour cutoff rule.
     */
    public function requestRelease(Request $request): RedirectResponse
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'dive_date' => 'required|date',
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $coach = Auth::user();
        $batch = Batch::findOrFail($request->input('batch_id'));
        $diveDate = Carbon::parse($request->input('dive_date'))->startOfDay();

        // 48-Hour Cutoff Enforcement (§5.3 / PRD Q3)
        $diveStart = $diveDate->copy()->setTime(9, 30);
        $hoursUntilDive = Carbon::now()->diffInHours($diveStart, false);

        if ($hoursUntilDive <= 48) {
            return back()->with('error', "Emergency release requests cannot be submitted within 48 hours of dive departure (Lead time: {$hoursUntilDive}h). For urgent situations, please contact Camp Operations directly.");
        }

        // Check if existing pending request
        $existing = AssignmentReleaseRequest::where('coach_id', $coach->id)
            ->where('batch_id', $batch->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have a pending release request submitted for this session.');
        }

        AssignmentReleaseRequest::create([
            'coach_id' => $coach->id,
            'batch_id' => $batch->id,
            'dive_date' => $diveDate,
            'reason' => $request->input('reason'),
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Your emergency release request has been submitted to Camp Admin for review.');
    }
}
