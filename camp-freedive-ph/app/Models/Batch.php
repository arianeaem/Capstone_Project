<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'batch_code',
        'start_date',
        'end_date',
        'lifecycle_status',
        'risk_classification',
        'max_capacity',
        'status',
        'capacity_note',
        'notes',
        'created_by',
        'closed_at',
        'cancelled_at',
        'cancellation_reason',
        'completed_at',
        'archived_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'archived_at' => 'datetime',
        'max_capacity' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BatchStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function participantAssignments(): HasMany
    {
        return $this->hasMany(ParticipantAssignment::class, 'batch_id');
    }

    public function coachAssignments(): HasMany
    {
        return $this->hasMany(ParticipantAssignment::class, 'batch_id');
    }

    public function activeParticipantAssignments(): HasMany
    {
        return $this->hasMany(ParticipantAssignment::class, 'batch_id')->where('status', 'assigned');
    }

    public function openings(): HasMany
    {
        return $this->hasMany(CoachOpening::class, 'batch_id');
    }

    public function coachRequests(): HasMany
    {
        return $this->hasMany(CoachRequest::class, 'batch_id');
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(BatchRiskAssessment::class, 'batch_id')->orderBy('assessed_at', 'desc');
    }

    public function riskAssessment()
    {
        return $this->hasOne(BatchRiskAssessment::class, 'batch_id')->latestOfMany('assessed_at');
    }

    public function manualOverrides(): HasMany
    {
        return $this->hasMany(ManualOverride::class, 'batch_id')->orderBy('created_at', 'desc');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'batch_id')->orderBy('sent_at', 'desc');
    }

    public function releaseRequests(): HasMany
    {
        return $this->hasMany(AssignmentReleaseRequest::class, 'batch_id')->orderBy('requested_at', 'desc');
    }

    public function getLatestDay1AssessmentAttribute(): ?BatchRiskAssessment
    {
        return $this->riskAssessments()
            ->where('day_number', 1)
            ->with(['amHourlyAssessments', 'pmHourlyAssessments'])
            ->first();
    }

    public function getLatestDay2AssessmentAttribute(): ?BatchRiskAssessment
    {
        return $this->riskAssessments()
            ->where('day_number', 2)
            ->with(['amHourlyAssessments', 'pmHourlyAssessments'])
            ->first();
    }

    public function getLatestManualOverrideAttribute(): ?ManualOverride
    {
        return $this->manualOverrides()->first();
    }

    /**
     * Display name of the batch (Batch Number as primary).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->batch_number;
    }

    /**
     * Distinct coaches assigned to this batch.
     */
    public function getAssignedCoachesAttribute(): Collection
    {
        $fromAssignments = $this->activeParticipantAssignments()
            ->with('coach')
            ->get()
            ->pluck('coach')
            ->unique('id')
            ->filter();

        // Also detect coaches assigned for this batch date when participants are 0
        $startDateStr = $this->start_date ? $this->start_date->format('Y-m-d') : null;
        if ($startDateStr) {
            $batchNum = $this->batch_number;
            $batchCode = $this->batch_code;

            $fromAvailabilities = User::where('role', 'coach')
                ->whereHas('coachAvailabilities', function ($q) use ($startDateStr, $batchNum, $batchCode) {
                    $q->whereDate('date', $startDateStr)
                      ->where('status', 'assigned')
                      ->where(function ($sub) use ($batchNum, $batchCode) {
                          if ($batchNum) $sub->where('notes', 'like', "%{$batchNum}%");
                          if ($batchCode) $sub->orWhere('notes', 'like', "%{$batchCode}%");
                      });
                })->get();

            return $fromAssignments->merge($fromAvailabilities)->unique('id')->values();
        }

        return $fromAssignments;
    }

    public const MAX_CAPACITY = 45;

    public function getAssignedCoachesCountAttribute(): int
    {
        return $this->assigned_coaches->count();
    }

    /**
     * Compute batch operational capacity: 45 pax ceiling.
     */
    public function getComputedCapacityAttribute(): int
    {
        return $this->max_capacity ?: self::MAX_CAPACITY;
    }

    /**
     * Check if coach staffing is pending (has participants/bookings but 0 coaches assigned).
     */
    public function getIsCoachPendingAttribute(): bool
    {
        if ($this->total_participants_count === 0) {
            return false;
        }

        return $this->assigned_coaches_count === 0;
    }

    /**
     * Total participants in active confirmed bookings.
     */
    public function getTotalParticipantsCountAttribute(): int
    {
        return (int) $this->bookings()
            ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment'])
            ->withCount('participants')
            ->get()
            ->sum('participants_count');
    }

    /**
     * Remaining student slots (out of 45).
     */
    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->computed_capacity - $this->total_participants_count);
    }

    /**
     * Occupancy percentage out of 45 max capacity.
     */
    public function getOccupancyPercentageAttribute(): ?int
    {
        if ($this->computed_capacity === 0) {
            return null;
        }

        return (int) min(100, round(($this->total_participants_count / $this->computed_capacity) * 100));
    }

    /**
     * Total collected amount for bookings in this batch.
     */
    public function getTotalCollectedAmountAttribute(): float
    {
        return (float) Payment::whereIn('booking_id', $this->bookings()->pluck('id'))
            ->whereIn('status', ['completed', 'paid'])
            ->sum('amount');
    }

    /**
     * Outstanding balance bookings count.
     */
    public function getOutstandingBalanceBookingsCountAttribute(): int
    {
        return $this->bookings()
            ->whereNotIn('status', ['cancelled_by_camp', 'cancelled_by_guest', 'cancelled', 'pending_downpayment'])
            ->where('balance_amount', '>', 0)
            ->count();
    }

    /**
     * Pending refunds count for bookings in this batch.
     */
    public function getPendingRefundsCountAttribute(): int
    {
        return RefundRequest::whereIn('booking_id', $this->bookings()->pluck('id'))
            ->where('status', 'pending')
            ->count();
    }

    /**
     * Check if weather is High or Critical risk.
     */
    public function getIsCriticalOrHighRiskAttribute(): bool
    {
        return in_array($this->risk_classification, ['high_risk', 'critical_risk']);
    }

    /**
     * Needs attention flag (Critical weather OR approaching soon with 0 coaches).
     */
    public function getNeedsAttentionAttribute(): bool
    {
        if ($this->is_critical_or_high_risk) {
            return true;
        }

        if ($this->is_coach_pending && $this->start_date <= Carbon::now()->addDays(7) && in_array($this->status, ['confirmed', 'open'])) {
            return true;
        }

        return false;
    }

    public function getBatchNumberAttribute(): string
    {
        if (!empty($this->batch_code)) {
            if (preg_match('/^batch\s*#?\s*(\d+)/i', $this->batch_code, $matches)) {
                return 'Batch ' . $matches[1];
            }
            $cleaned = preg_replace('/^BATCH[-#\s]*/i', '', $this->batch_code);
            return is_numeric($cleaned) ? 'Batch ' . $cleaned : $this->batch_code;
        }
        if (!empty($this->name) && preg_match('/^Batch\s*(\d+)/i', $this->name, $matches)) {
            return 'Batch ' . $matches[1];
        }
        return 'Batch ' . $this->id;
    }

    public function getStatusBadgeAttribute(): array
    {
        $st = $this->status ?: $this->lifecycle_status ?: 'confirmed';
        return match ($st) {
            'confirmed', 'open' => [
                'label' => 'Confirmed',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'completed' => [
                'label' => 'Completed',
                'class' => 'bg-gray-100 text-gray-700 border-gray-300',
            ],
            'rescheduled' => [
                'label' => 'Rescheduled',
                'class' => 'bg-amber-50 text-amber-800 border-amber-300',
            ],
            'cancelled_by_camp', 'cancelled' => [
                'label' => 'Cancelled by Camp',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $st)),
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
        };
    }

    public function getRiskBadgeAttribute(): array
    {
        $key = strtolower(str_replace([' ', '-'], '_', $this->risk_classification ?: 'safe'));
        return match ($key) {
            'very_safe' => [
                'label' => 'Very Safe',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'safe' => [
                'label' => 'Safe',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'moderate' => [
                'label' => 'Moderate',
                'class' => 'bg-amber-50 text-amber-800 border-amber-300',
            ],
            'high_risk' => [
                'label' => 'High Risk',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'critical_risk' => [
                'label' => 'Critical',
                'class' => 'bg-red-100 text-red-800 border-red-300',
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $this->risk_classification ?: 'Safe')),
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
        };
    }
}
