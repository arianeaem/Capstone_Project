<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchRiskAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'day_number',
        'dive_date',
        'lead_time_hours',
        'overall_classification',
        'weighted_score_pct',
        'recommended_action',
        'worst_window',
        'worst_hour',
        'override_triggered',
        'override_details',
        'assessed_by',
        'assessed_at',
    ];

    protected $casts = [
        'dive_date' => 'date',
        'lead_time_hours' => 'decimal:2',
        'weighted_score_pct' => 'decimal:2',
        'worst_hour' => 'datetime',
        'override_triggered' => 'boolean',
        'override_details' => 'array',
        'assessed_at' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:sP');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function hourlyAssessments(): HasMany
    {
        return $this->hasMany(HourlyAssessment::class, 'risk_assessment_id')->orderBy('forecast_time', 'asc');
    }

    public function amHourlyAssessments(): HasMany
    {
        return $this->hasMany(HourlyAssessment::class, 'risk_assessment_id')
            ->where('window_type', 'am')
            ->orderBy('forecast_time', 'asc');
    }

    public function pmHourlyAssessments(): HasMany
    {
        return $this->hasMany(HourlyAssessment::class, 'risk_assessment_id')
            ->where('window_type', 'pm')
            ->orderBy('forecast_time', 'asc');
    }

    public function getClassificationBadgeAttribute(): array
    {
        return match ($this->overall_classification) {
            'Very Safe' => [
                'label' => 'Very Safe',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'bg' => 'bg-emerald-500',
            ],
            'Safe' => [
                'label' => 'Safe',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'bg' => 'bg-emerald-500',
            ],
            'Moderate' => [
                'label' => 'Moderate',
                'class' => 'bg-amber-50 text-amber-800 border-amber-300',
                'bg' => 'bg-amber-500',
            ],
            'High Risk' => [
                'label' => 'High Risk',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200',
                'bg' => 'bg-rose-500',
            ],
            'Critical Risk' => [
                'label' => 'Critical Risk',
                'class' => 'bg-red-100 text-red-800 border-red-300',
                'bg' => 'bg-red-600',
            ],
            default => [
                'label' => $this->overall_classification ?: 'Not Assessed',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
                'bg' => 'bg-gray-400',
            ],
        };
    }

    public function getReliabilityAttribute(): array
    {
        $daysOut = max(0, (float) ($this->lead_time_hours / 24.0));
        return \App\Services\WeatherForecastService::getReliabilityCategory($daysOut);
    }
}
