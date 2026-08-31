<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BookingParticipant;
use App\Models\CoachOpening;
use App\Models\CoachRequest;
use App\Models\User;
use App\Services\CoachMatchingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachMatchingController extends Controller
{
    public function __construct(
        protected CoachMatchingService $matchingService
    ) {}

    /**
     * Page 3: Students Needing a Coach (Matching Queue & Studio).
     */
    public function matching(): View
    {
        // 1. Fetch unassigned participants from active bookings
        $unassignedParticipants = BookingParticipant::with(['booking.batch'])
            ->whereHas('booking', function ($q) {
                $q->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'completed', 'no_show']);
            })
            ->whereDoesntHave('activeAssignment')
            ->get();

        // 2. Group by Batch / Dive Date
        $batches = Batch::with(['bookings.participants'])
            ->whereNotIn('status', ['completed', 'cancelled_by_camp'])
            ->orderBy('start_date', 'asc')
            ->get();

        // Fetch all active coaches
        $activeCoaches = User::where('role', 'coach')
            ->where('status', 'active')
            ->with(['coachAvailabilities'])
            ->get();

        // Prepare batch matching groups with proposal options and balanced initial drafts
        $batchGroups = $batches->map(function ($batch) use ($unassignedParticipants, $activeCoaches) {
            $studentsInBatch = $unassignedParticipants->filter(function ($p) use ($batch) {
                return $p->booking && ($p->booking->batch_id === $batch->id || $p->booking->start_date->isSameDay($batch->start_date));
            })->values();

            $unassignedCount = $studentsInBatch->count();
            if ($unassignedCount === 0) {
                return null;
            }

            // Class breakdown count
            $classCounts = $studentsInBatch->groupBy(fn($p) => strtolower($p->booking->class_type ?? 'discovery'))
                ->map(fn($group) => $group->count());

            // Available coaches for this batch dive date
            $availableCoaches = $activeCoaches->filter(function ($coach) use ($batch) {
                $dateStr = $batch->start_date->format('Y-m-d');
                $avail = $coach->coachAvailabilities->firstWhere('date', $dateStr);
                return !$avail || in_array($avail->status, ['available', 'assigned']);
            })->map(function ($coach) use ($batch) {
                $currentLoad = $coach->assignedCountForDate($batch->start_date);
                return [
                    'id' => $coach->id,
                    'name' => $coach->name,
                    'email' => $coach->email,
                    'phone' => $coach->phone,
                    'current_load' => $currentLoad,
                    'is_full' => $currentLoad >= 4,
                    'coach_model' => $coach,
                ];
            })->values();

            // Candidate Coach Split Options (§6.3 Step 2)
            $coachOptions = $this->matchingService->proposeCoachCountOptions(
                $unassignedCount,
                $availableCoaches->count()
            );

            // Default initial balanced draft (§6.3 Step 3) using recommended coach count
            $recommendedOption = collect($coachOptions)->firstWhere('is_recommended', true) ?? collect($coachOptions)->first();
            $recommendedCount = $recommendedOption['coach_count'] ?? min(1, $availableCoaches->count());
            $selectedCoachModels = $availableCoaches->take($recommendedCount)->pluck('coach_model')->all();

            $draftResult = $this->matchingService->generateBalancedDraft(
                $studentsInBatch,
                $selectedCoachModels
            );

            // Check if open broadcast exists
            $openBroadcast = CoachOpening::where('batch_id', $batch->id)
                ->where('status', 'open')
                ->first();

            // Format students for Alpine JS interactive studio
            $studentsData = $studentsInBatch->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'age' => $p->age,
                    'class_type' => ucfirst($p->booking->class_type ?? 'Discovery'),
                    'class_slug' => strtolower($p->booking->class_type ?? 'discovery'),
                    'swimmer_status' => ucfirst(str_replace('_', ' ', $p->swimmer_status)),
                    'health_condition' => $p->health_condition ?: 'None declared',
                    'booking_number' => $p->booking->booking_number,
                ];
            });

            return [
                'batch' => $batch,
                'unassigned_students' => $studentsInBatch,
                'unassigned_count' => $unassignedCount,
                'class_counts' => $classCounts,
                'available_coaches' => $availableCoaches,
                'coach_options' => $coachOptions,
                'initial_draft' => $draftResult['draft'],
                'is_balanced' => $draftResult['is_balanced'],
                'students_data' => $studentsData,
                'open_broadcast' => $openBroadcast,
            ];
        })->filter()->values();

        // Pending Coach Requests count
        $pendingRequestsCount = CoachRequest::where('status', 'pending')->count();

        return view('admin.coaches.matching', compact(
            'batchGroups',
            'pendingRequestsCount'
        ));
    }

    /**
     * Batch Assignment: Commit balanced multi-coach distribution for a batch.
     */
    public function batchAssign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'assignments' => 'required|array|min:1', // coach_id => [participant_ids...]
            'assignments.*' => 'nullable|array',
            'exception_note' => 'nullable|string|max:500',
        ]);

        try {
            $batch = Batch::findOrFail($validated['batch_id']);

            $result = $this->matchingService->saveBatchBalancedAssignments(
                $batch,
                $validated['assignments'],
                auth()->user(),
                $validated['exception_note'] ?? null
            );

            $msg = "✓ Balanced match confirmed: {$result['total_assigned']} student(s) successfully assigned across {$result['coaches_count']} coach(es).";
            if ($result['is_imbalanced']) {
                $msg .= " ℹ️ Imbalanced split recorded as an intentional exception.";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign selected students to a single coach (Quick Single-Coach Assignment).
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:booking_participants,id',
            'coach_id' => 'required|exists:users,id',
            'batch_id' => 'required|exists:batches,id',
        ]);

        try {
            $coach = User::findOrFail($validated['coach_id']);
            $batch = Batch::findOrFail($validated['batch_id']);

            $result = $this->matchingService->assignStudentsToCoach(
                $validated['participant_ids'],
                $coach,
                $batch,
                auth()->user()
            );

            $msg = "✓ Assigned {$result['assigned_count']} student(s) to Coach {$coach->name}. (Current load: {$result['total_load']}/4 Pax)";
            if ($result['is_ratio_override']) {
                $msg .= " Note: Total load exceeds standard 4:1 ratio (logged as an Override Exception).";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Broadcast an open slot to the Coach Portal when no coach is available.
     */
    public function broadcastOpening(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $batch = Batch::findOrFail($validated['batch_id']);

            $this->matchingService->postOpeningToPortal(
                $batch,
                $batch->start_date,
                auth()->user(),
                $validated['notes'] ?? null
            );

            return back()->with('success', "✓ Open slot for {$batch->batch_code} ({$batch->start_date->format('M d, Y')}) has been posted to the Coach Portal.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Page 4: Review Coach Requests queue.
     */
    public function requests(): View
    {
        $requests = CoachRequest::with(['opening.batch', 'coach', 'batch'])
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingRequests = $requests->where('status', 'pending')->groupBy('batch_id');
        $reviewedRequests = $requests->where('status', '!=', 'pending');

        return view('admin.coaches.requests', compact(
            'pendingRequests',
            'reviewedRequests'
        ));
    }

    /**
     * Approve a coach request for an open slot.
     */
    public function approveRequest(CoachRequest $coachRequest): RedirectResponse
    {
        try {
            $this->matchingService->approveCoachRequest($coachRequest, auth()->user());

            $batch = $coachRequest->batch;
            $headcount = $batch->booked_headcount ?: 4;
            $coachesNeeded = max(1, (int) ceil($headcount / 4));
            $approvedCount = CoachRequest::where('batch_id', $batch->id)->where('status', 'approved')->count();

            if ($approvedCount >= $coachesNeeded) {
                $msg = "✓ Approved Coach {$coachRequest->coach->name}. All {$coachesNeeded} coach slot(s) for batch {$batch->batch_code} are now filled!";
            } else {
                $remaining = $coachesNeeded - $approvedCount;
                $msg = "✓ Approved Coach {$coachRequest->coach->name} for {$batch->batch_code} ({$approvedCount} of {$coachesNeeded} slots filled). {$remaining} more coach(es) needed.";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve an emergency assignment release request.
     */
    public function approveReleaseRequest(Request $request, \App\Models\AssignmentReleaseRequest $releaseRequest): RedirectResponse
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($releaseRequest, $request) {
                $releaseRequest->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'review_notes' => $request->input('notes', 'Approved by Camp Administration.'),
                ]);

                // Unassign the coach's students for that batch
                \App\Models\ParticipantAssignment::where('coach_id', $releaseRequest->coach_id)
                    ->where('batch_id', $releaseRequest->batch_id)
                    ->delete();

                // Set coach availability for that date to unavailable
                \App\Models\CoachAvailability::where('coach_id', $releaseRequest->coach_id)
                    ->where('date', $releaseRequest->dive_date)
                    ->update(['status' => 'unavailable']);

                // Pair date
                $pairDate = $releaseRequest->dive_date->isSaturday() 
                    ? $releaseRequest->dive_date->copy()->addDay() 
                    : $releaseRequest->dive_date->copy()->subDay();

                \App\Models\CoachAvailability::where('coach_id', $releaseRequest->coach_id)
                    ->where('date', $pairDate)
                    ->update(['status' => 'unavailable']);
            });

            return back()->with('success', "✓ Approved release request for Coach {$releaseRequest->coach->name}. Students have been moved back to the matching queue.");
        } catch (Exception $e) {
            return back()->with('error', 'Failed to approve release request: ' . $e->getMessage());
        }
    }

    /**
     * Reject an emergency assignment release request.
     */
    public function rejectReleaseRequest(Request $request, \App\Models\AssignmentReleaseRequest $releaseRequest): RedirectResponse
    {
        $releaseRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('notes', 'Request could not be accommodated due to staffing constraints.'),
        ]);

        return back()->with('success', "Release request for Coach {$releaseRequest->coach->name} has been rejected.");
    }
}
