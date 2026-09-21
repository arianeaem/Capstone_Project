<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Coach;
use App\Services\AuditLogger;
use App\Services\CoachAssignmentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CoachAssignmentController extends Controller
{
    public function __construct(
        protected CoachAssignmentService $assignmentService
    ) {}

    /**
     * Display batches and coach assignments board.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today()->toDateString();

        $query = Batch::with(['activeAssignments.coach', 'activeAssignments.assignedByUser'])
            ->where(function ($q) use ($today) {
                // Keep if NOT done (upcoming / today)
                $q->where(function ($sub) use ($today) {
                    $sub->whereDate('end_date', '>=', $today)
                        ->whereNotIn('status', ['completed', 'cancelled_by_camp']);
                })
                // OR keep if it HAS active participants
                ->orWhereHas('bookings', function ($b) {
                    $b->whereNotIn('status', [
                        'cancelled_by_camp',
                        'cancelled_by_guest',
                        'cancelled',
                        'pending_downpayment'
                    ])->has('participants');
                });
            })
            ->orderBy('start_date', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $batches = $query->paginate(12)->withQueryString();

        $activeCoaches = Coach::where('status', 'active')->orderBy('full_name')->get();

        return view('admin.coaches.assignments', compact('batches', 'activeCoaches'));
    }

    /**
     * Create a new 2D1N dive batch schedule.
     */
    public function storeBatch(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'batch_code' => ['nullable', 'string', 'max:50', 'unique:batches,batch_code'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $batchCode = $validated['batch_code'] ?: ('BATCH-' . date('Ymd', strtotime($validated['start_date'])));

        $batch = Batch::create([
            'batch_code' => $batchCode,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'max_capacity' => 0, // Starts at 0 until coaches are assigned
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::log(
            'BATCH_SCHEDULE_CREATED',
            "2D1N Batch {$batch->batch_code} ({$batch->start_date->format('M d')} - {$batch->end_date->format('M d, Y')}) created by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "Batch {$batch->batch_code} created! Assign coaches to enable student capacity.");
    }

    /**
     * Assign a coach to a batch.
     */
    public function assign(Request $request, Batch $batch): RedirectResponse
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'coach_id' => ['required', 'exists:coaches,id'],
        ]);

        $coach = Coach::findOrFail($validated['coach_id']);

        try {
            $result = $this->assignmentService->assign($coach, $batch, $currentUser);

            AuditLogger::log(
                'COACH_ASSIGNED',
                "Coach {$coach->full_name} assigned to Batch {$batch->batch_code} (Capacity: {$result['new_capacity']} pax) by {$currentUser->name}",
                $currentUser,
                $currentUser->name,
                $request
            );

            $msg = "{$coach->full_name} assigned to Batch {$batch->batch_code}. Total session capacity is now {$result['new_capacity']} students (4 pax/coach).";
            if (!empty($result['warning'])) {
                $msg .= " " . $result['warning'];
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unassign a coach from a batch.
     */
    public function unassign(Request $request, Batch $batch, Coach $coach): RedirectResponse
    {
        $currentUser = Auth::user();

        $this->assignmentService->unassign($coach, $batch);

        AuditLogger::log(
            'COACH_UNASSIGNED',
            "Coach {$coach->full_name} unassigned from Batch {$batch->batch_code} (Capacity updated to {$batch->computed_capacity} pax) by {$currentUser->name}",
            $currentUser,
            $currentUser->name,
            $request
        );

        return back()->with('success', "{$coach->full_name} unassigned from Batch {$batch->batch_code}. Capacity updated to {$batch->computed_capacity} pax.");
    }
}
