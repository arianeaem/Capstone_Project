<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Booking;
use App\Services\BatchManagementService;
use App\Services\DemandForecastService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchManagementController extends Controller
{
    public function __construct(
        protected BatchManagementService $batchService,
        protected DemandForecastService $forecastService
    ) {}

    /**
     * Page 1: Batch List.
     */
    public function index(Request $request): View
    {
        $query = Batch::with(['bookings.participants', 'activeParticipantAssignments.coach']);

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

        // Filter: Search Batch Number or Notes
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sort = $request->input('sort', 'date_asc');
        match ($sort) {
            'date_desc' => $query->orderBy('start_date', 'desc')->orderBy('created_at', 'desc'),
            'date_asc' => $query->orderBy('start_date', 'asc')->orderBy('created_at', 'desc'),
            'batch_asc' => $query->orderBy('batch_code', 'asc'),
            'batch_desc' => $query->orderBy('batch_code', 'desc'),
            'created_desc' => $query->latest('created_at'),
            'created_asc' => $query->oldest('created_at'),
            default => $query->orderBy('start_date', 'asc')->orderBy('created_at', 'desc'),
        };

        $batches = $query->get();

        // Staffing Status Filter (in-memory computed)
        if ($request->filled('staffing')) {
            if ($request->input('staffing') === 'pending') {
                $batches = $batches->filter(fn($b) => $b->is_coach_pending);
            } elseif ($request->input('staffing') === 'staffed') {
                $batches = $batches->filter(fn($b) => !$b->is_coach_pending);
            }
        }

        // Capacity-based sorting (in-memory computed)
        if ($sort === 'capacity_desc') {
            $batches = $batches->sortByDesc(fn($b) => $b->total_participants_count)->values();
        } elseif ($sort === 'capacity_asc') {
            $batches = $batches->sortBy(fn($b) => $b->total_participants_count)->values();
        }

        // Needs Attention count
        $attentionCount = $batches->filter(fn($b) => $b->needs_attention)->count();

        // Confirmed Bookings that don't have an assigned batch yet (sorted newest first)
        $unbatchedBookings = Booking::whereNull('batch_id')
            ->where('status', 'confirmed')
            ->with('participants')
            ->orderBy('created_at', 'desc')
            ->get();
        $unbatchedCount = $unbatchedBookings->count();
        $unbatchedPaxCount = $unbatchedBookings->sum(fn($b) => $b->participants->count());

        // Paginate batches collection
        $page = (int) $request->input('page', 1);
        $perPage = max(5, min(100, (int) $request->input('per_page', 10)));
        $total = $batches->count();
        $batches = new \Illuminate\Pagination\LengthAwarePaginator(
            $batches->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.batches.index', compact(
            'batches', 
            'attentionCount', 
            'unbatchedBookings', 
            'unbatchedCount', 
            'unbatchedPaxCount'
        ));
    }


    /**
     * Page 2: Create Batch Form.
     */
    public function create(Request $request): View
    {
        $defaultDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now()->next(Carbon::SATURDAY);
        $defaultEndDate = $defaultDate->copy()->addDay();
        $defaultStartDateStr = $defaultDate->format('Y-m-d');
        $defaultEndDateStr = $defaultEndDate->format('Y-m-d');
        $batchCount = Batch::count();
        $defaultBatchNumber = 'Batch ' . ($batchCount + 1);

        $unbatchedBookings = $this->batchService->getUnbatchedBookingsForDate($defaultDate);

        $initialBookings = $unbatchedBookings->map(function ($b) {
            return [
                'id' => $b->id,
                'booking_number' => $b->booking_number,
                'contact_name' => $b->contact_name,
                'class_type' => ucfirst($b->class_type ?? 'Discovery'),
                'participants_count' => $b->participants->count(),
                'total_amount' => number_format($b->total_amount, 2),
            ];
        })->values()->all();

        $selectedIds = $unbatchedBookings->pluck('id')->values()->all();
        $initialStaffingRec = $this->forecastService->getStaffingRecommendationForDate($defaultDate);

        $existingBatches = Batch::all()->map(function ($b) {
            return [
                'id' => $b->id,
                'batch_number' => $b->batch_number,
                'name' => $b->name,
                'batch_code' => $b->batch_code,
                'start_date' => $b->start_date->format('Y-m-d'),
                'end_date' => $b->end_date->format('Y-m-d'),
                'status' => $b->status,
                'participants_count' => $b->total_participants_count,
                'coaches_count' => $b->assigned_coaches_count,
            ];
        })->values()->all();

        return view('admin.batches.create', compact(
            'defaultDate',
            'defaultStartDateStr',
            'defaultEndDateStr',
            'defaultBatchNumber',
            'unbatchedBookings',
            'initialBookings',
            'selectedIds',
            'existingBatches',
            'initialStaffingRec'
        ));
    }

    /**
     * AJAX endpoint to fetch unbatched bookings when date changes in create form.
     */
    public function unbatchedBookings(Request $request): JsonResponse
    {
        $dateStr = $request->input('date', Carbon::today()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);
        $bookings = $this->batchService->getUnbatchedBookingsForDate($date);
        $batchCount = Batch::count();
        $mlRec = $this->forecastService->getStaffingRecommendationForDate($date);

        $existingForDate = Batch::whereDate('start_date', $date)->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'batch_number' => $b->batch_number,
                'name' => $b->name,
                'batch_code' => $b->batch_code,
                'status' => $b->status,
                'participants_count' => $b->total_participants_count,
                'coaches_count' => $b->assigned_coaches_count,
            ];
        })->values()->all();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'suggested_batch_number' => 'Batch ' . ($batchCount + 1),
            'suggested_name' => 'Batch ' . ($batchCount + 1),
            'suggested_code' => 'Batch ' . ($batchCount + 1),
            'count' => $bookings->count(),
            'existing_batches' => $existingForDate,
            'ml_recommendation' => $mlRec,
            'bookings' => $bookings->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_number' => $b->booking_number,
                    'contact_name' => $b->contact_name,
                    'class_type' => ucfirst($b->class_type ?? 'Discovery'),
                    'participants_count' => $b->participants->count(),
                    'total_amount' => number_format($b->total_amount, 2),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created Batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_number' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'batch_code' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'risk_classification' => 'nullable|string|in:very_safe,safe,moderate,high_risk,critical_risk',
            'capacity_note' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'booking_ids' => 'nullable|array',
            'booking_ids.*' => 'exists:bookings,id',
        ]);

        if (!empty($validated['batch_number'])) {
            $validated['batch_code'] = $validated['batch_number'];
            $validated['name'] = $validated['batch_number'];
        }

        // Check if an existing batch already exists for the same start date to prevent duplicate date batches
        $existingBatch = Batch::whereDate('start_date', $validated['start_date'])->first();
        if ($existingBatch) {
            $bookingIds = $request->input('booking_ids', []);
            if (!empty($bookingIds)) {
                Booking::whereIn('id', $bookingIds)->update(['batch_id' => $existingBatch->id]);
            }

            return redirect()->route('admin.batches.show', $existingBatch)
                ->with('info', "A batch for {$existingBatch->start_date->format('M d, Y')} already exists ({$existingBatch->batch_code}). Redirected to existing batch" . (!empty($bookingIds) ? " and " . count($bookingIds) . " booking(s) were assigned to it." : "."));
        }

        try {
            $batch = $this->batchService->createBatch(
                $validated,
                $request->input('booking_ids', []),
                auth()->user()
            );

            return redirect()->route('admin.batches.show', $batch)
                ->with('success', "Batch {$batch->name} ({$batch->batch_code}) created with " . count($request->input('booking_ids', [])) . " linked booking(s).");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Batch creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Page 3: Batch Detail View.
     */
    public function show(Batch $batch): View
    {
        $batch->load([
            'bookings.participants.assignments.coach',
            'activeParticipantAssignments.coach',
            'statusLogs.changer',
            'creator',
        ]);

        // Pre-trip assigned coaches for this batch
        $assignedCoaches = $batch->assigned_coaches;

        // Unassigned students count in this batch
        $unassignedStudentsCount = $batch->bookings->flatMap->participants
            ->filter(fn($p) => !$p->activeAssignment)
            ->count();

        // Other existing batches for "Move Booking" modal
        $otherBatches = Batch::where('id', '!=', $batch->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.batches.show', compact(
            'batch',
            'assignedCoaches',
            'unassignedStudentsCount',
            'otherBatches'
        ));
    }

    /**
     * Quick on-site pod assignment for a participant using the batch's pre-trip assigned coaches.
     */
    public function assignParticipant(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'participant_id' => 'required|exists:booking_participants,id',
            'coach_id' => 'nullable|exists:users,id',
        ]);

        $participant = \App\Models\BookingParticipant::findOrFail($validated['participant_id']);

        if ($participant->booking?->batch_id !== $batch->id) {
            return back()->with('error', 'Participant does not belong to this batch.');
        }

        $diveDate = $batch->start_date;
        $assignedBy = auth()->user();

        \Illuminate\Support\Facades\DB::transaction(function () use ($batch, $participant, $validated, $diveDate, $assignedBy) {
            $oldAssignment = \App\Models\ParticipantAssignment::where('participant_id', $participant->id)
                ->where('status', 'assigned')
                ->first();

            $oldCoachId = $oldAssignment?->coach_id;

            if (empty($validated['coach_id'])) {
                if ($oldAssignment) {
                    $oldAssignment->delete();
                    \App\Models\AssignmentLog::create([
                        'participant_id' => $participant->id,
                        'old_coach_id' => $oldCoachId,
                        'new_coach_id' => null,
                        'changed_by' => $assignedBy->id,
                        'reason' => 'On-site unassigned from pod.',
                    ]);
                }
            } else {
                $newCoach = \App\Models\User::findOrFail($validated['coach_id']);

                if ($oldAssignment) {
                    $oldAssignment->update([
                        'coach_id' => $newCoach->id,
                        'batch_id' => $batch->id,
                        'dive_date' => $diveDate,
                        'assigned_by' => $assignedBy->id,
                        'assigned_at' => now(),
                    ]);
                } else {
                    \App\Models\ParticipantAssignment::create([
                        'participant_id' => $participant->id,
                        'booking_id' => $participant->booking_id,
                        'coach_id' => $newCoach->id,
                        'batch_id' => $batch->id,
                        'dive_date' => $diveDate,
                        'assigned_by' => $assignedBy->id,
                        'assigned_at' => now(),
                        'status' => 'assigned',
                    ]);
                }

                \App\Models\AssignmentLog::create([
                    'participant_id' => $participant->id,
                    'old_coach_id' => $oldCoachId,
                    'new_coach_id' => $newCoach->id,
                    'changed_by' => $assignedBy->id,
                    'reason' => 'On-site pod assignment during session.',
                ]);
            }
        });

        $coachName = !empty($validated['coach_id']) ? \App\Models\User::find($validated['coach_id'])?->name : 'Shared Pool';
        return back()->with('success', "Assigned {$participant->name} to {$coachName}.");
    }

    /**
     * Update whole-batch status with cascade behavior.
     */
    public function updateStatus(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,completed,rescheduled,cancelled_by_camp',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $this->batchService->updateStatus(
                $batch,
                $validated['status'],
                auth()->user(),
                $validated['note'] ?? null
            );

            $actionLabel = match ($validated['status']) {
                'cancelled_by_camp' => 'Cancelled by Camp (Full refund eligibility triggered for all connected bookings)',
                'completed' => 'Marked as Completed',
                'rescheduled' => 'Rescheduled (Customer email notifications dispatched)',
                default => 'Updated to ' . ucfirst($validated['status']),
            };

            return back()->with('success', "Batch status updated: {$actionLabel}.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Move an individual booking from this batch to another batch.
     */
    public function moveBooking(Request $request, Batch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'target_batch_id' => 'nullable|exists:batches,id',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $booking = Booking::findOrFail($validated['booking_id']);
            $targetBatch = $validated['target_batch_id'] ? Batch::findOrFail($validated['target_batch_id']) : null;

            $this->batchService->moveBooking(
                $booking,
                $targetBatch,
                auth()->user(),
                $validated['reason'] ?? null
            );

            return back()->with('success', "Booking {$booking->booking_number} moved successfully.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
