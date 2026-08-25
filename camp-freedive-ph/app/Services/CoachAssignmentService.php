<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class CoachAssignmentService
{
    /**
     * Assign a coach to a 2D1N dive batch with certification & conflict validation.
     *
     * @throws Exception
     */
    public function assign(Coach $coach, Batch $batch, User $assignedBy): array
    {
        // 1. Status Check
        if ($coach->status !== 'active') {
            throw new Exception("Cannot assign coach with status '{$coach->status}'. Coach must be active.");
        }

        // 2. Certification Expiry Validation (evaluated against batch start date)
        if ($coach->isExpired($batch->start_date)) {
            throw new Exception("Cannot assign coach. Certification expired on " . $coach->certification_expiry->format('M d, Y') . ", prior to this batch ({$batch->start_date->format('M d, Y')}).");
        }

        // 3. Double-Booking Prevention (Check overlapping active assignments)
        $hasOverlap = CoachAssignment::where('coach_id', $coach->id)
            ->whereNull('unassigned_at')
            ->where('batch_id', '!=', $batch->id)
            ->whereHas('batch', function ($q) use ($batch) {
                $q->where(function ($sub) use ($batch) {
                    $sub->whereBetween('start_date', [$batch->start_date, $batch->end_date])
                        ->orWhereBetween('end_date', [$batch->start_date, $batch->end_date])
                        ->orWhere(function ($inside) use ($batch) {
                            $inside->where('start_date', '<=', $batch->start_date)
                                   ->where('end_date', '>=', $batch->end_date);
                        });
                });
            })
            ->exists();

        if ($hasOverlap) {
            throw new Exception("Double-booking prevented: {$coach->full_name} is already assigned to another batch on overlapping dates.");
        }

        // 4. Duplicate in same batch check
        $alreadyInBatch = CoachAssignment::where('coach_id', $coach->id)
            ->where('batch_id', $batch->id)
            ->whereNull('unassigned_at')
            ->exists();

        if ($alreadyInBatch) {
            throw new Exception("{$coach->full_name} is already actively assigned to this batch.");
        }

        // 5. Advisory Availability Warning
        $warning = null;
        $dayOfWeek = $batch->start_date->dayOfWeek; // 0=Sun, 6=Sat
        $isRecurringAvail = $coach->availabilities()->where('day_of_week', $dayOfWeek)->where('is_available', true)->exists();
        $isBlackout = $coach->blackoutDates()->whereDate('blackout_date', $batch->start_date)->exists();

        if ($isBlackout) {
            $warning = "Note: Coach has marked {$batch->start_date->format('M d, Y')} as a blackout date. Assigned via staff override.";
        } elseif (!$isRecurringAvail && $coach->availabilities()->exists()) {
            $warning = "Note: Assigned outside stated recurring weekly availability. Staff override recorded.";
        }

        // 6. Execute Assignment & Recalculate Capacity
        $assignment = DB::transaction(function () use ($coach, $batch, $assignedBy) {
            $assignment = CoachAssignment::create([
                'coach_id' => $coach->id,
                'batch_id' => $batch->id,
                'assigned_by' => $assignedBy->id,
                'assigned_at' => now(),
            ]);

            // Recompute capacity: assigned_coaches_count * 4
            $batch->update([
                'max_capacity' => $batch->computed_capacity,
            ]);

            return $assignment;
        });

        return [
            'success' => true,
            'assignment' => $assignment,
            'warning' => $warning,
            'new_capacity' => $batch->computed_capacity,
        ];
    }

    /**
     * Unassign a coach from a batch.
     */
    public function unassign(Coach $coach, Batch $batch): bool
    {
        return DB::transaction(function () use ($coach, $batch) {
            CoachAssignment::where('coach_id', $coach->id)
                ->where('batch_id', $batch->id)
                ->whereNull('unassigned_at')
                ->update(['unassigned_at' => now()]);

            // Recompute capacity
            $batch->update([
                'max_capacity' => $batch->computed_capacity,
            ]);

            return true;
        });
    }
}
