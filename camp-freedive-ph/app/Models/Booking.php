<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'booking_number',
        'pin',
        'class_type',
        'is_certified_diver',
        'start_date',
        'end_date',
        'pickup_option',
        'pickup_location',
        'carpool_fee',
        'boat_dive',
        'boat_dive_fee',
        'lgu_fee',
        'environmental_fee',
        'subtotal',
        'total_amount',
        'downpayment_amount',
        'balance_amount',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_facebook',
        'status',
        'created_by',
    ];

    protected $casts = [
        'is_certified_diver' => 'boolean',
        'boat_dive' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'carpool_fee' => 'decimal:2',
        'boat_dive_fee' => 'decimal:2',
        'lgu_fee' => 'decimal:2',
        'environmental_fee' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'downpayment_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(BookingParticipant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function rescheduleRequests(): HasMany
    {
        return $this->hasMany(RescheduleRequest::class);
    }

    public function priceAdjustments(): HasMany
    {
        return $this->hasMany(BookingPriceAdjustment::class)->orderBy('id', 'asc');
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class)->orderBy('created_at', 'desc');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveConfirmed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            'confirmed',
            'rescheduled',
            'reschedule_requested',
            'cancellation_requested',
            'completed'
        ]);
    }

    public function getFormattedClassTypeAttribute(): string
    {
        return match ($this->class_type) {
            'discovery' => 'Discovery (Beginner Class)',
            'fundive' => 'Fundive ' . ($this->is_certified_diver ? '(Certified Diver)' : '(Non-Certified Diver)'),
            'refinement' => 'Refinement (Practice Dive)',
            default => ucfirst($this->class_type),
        };
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        $hasPayment = $this->payments()->whereIn('status', ['completed', 'paid'])->exists();
        $isRefunded = $this->payments()->whereIn('status', ['refunded', 'refund_requested'])->exists();

        if ($this->status === 'no_show') {
            return [
                'label' => 'Forfeited (No-Show)',
                'class' => 'bg-purple-100 text-purple-800 border-purple-200',
            ];
        }

        if ($isRefunded || in_array($this->status, ['cancelled_by_camp', 'cancelled_by_guest'])) {
            return [
                'label' => 'Refund Pending / Processed',
                'class' => 'bg-red-50 text-red-700 border-red-200',
            ];
        }

        if ($hasPayment) {
            return [
                'label' => 'Downpayment Paid',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ];
        }

        return [
            'label' => 'Unpaid',
            'class' => 'bg-gray-100 text-gray-700 border-gray-200',
        ];
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'confirmed' => [
                'label' => 'Confirmed',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'color' => '#34C759',
            ],
            'completed' => [
                'label' => 'Completed',
                'bg' => 'bg-gray-100 text-gray-700 border-gray-300',
                'class' => 'bg-gray-100 text-gray-700 border-gray-300',
                'color' => '#6E6E73',
            ],
            'rescheduled' => [
                'label' => 'Rescheduled',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'color' => '#FF8D28',
            ],
            'reschedule_requested' => [
                'label' => 'Reschedule Requested',
                'bg' => 'bg-yellow-50 text-yellow-800 border-yellow-300',
                'class' => 'bg-yellow-50 text-yellow-800 border-yellow-300',
                'color' => '#B45309',
            ],
            'cancellation_requested' => [
                'label' => 'Cancellation Requested',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-300',
                'class' => 'bg-rose-50 text-rose-700 border-rose-300',
                'color' => '#E11D48',
            ],
            'cancelled_by_camp' => [
                'label' => 'Cancelled by Camp',
                'bg' => 'bg-red-50 text-red-700 border-red-200',
                'class' => 'bg-red-50 text-red-700 border-red-200',
                'color' => '#FF3B3C',
            ],
            'cancelled_by_guest' => [
                'label' => 'Cancelled by Guest',
                'bg' => 'bg-rose-100 text-rose-800 border-rose-300',
                'class' => 'bg-rose-100 text-rose-800 border-rose-300',
                'color' => '#BE123C',
            ],
            'no_show' => [
                'label' => 'No-Show (Forfeited)',
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'color' => '#7E22CE',
            ],
            default => [
                'label' => ucfirst(str_replace('_', ' ', $this->status)),
                'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
                'class' => 'bg-gray-50 text-gray-700 border-gray-200',
                'color' => '#6E6E73',
            ],
        };
    }

    public function getStudentCountAttribute(): int
    {
        return $this->participants()->count() ?: 1;
    }

    public function getClassTypeLabelAttribute(): string
    {
        return $this->formatted_class_type;
    }

    public function getBatchDatesFormattedAttribute(): string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
        }
        return '2D1N Freediving Camp';
    }
}
