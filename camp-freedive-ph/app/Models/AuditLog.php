<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_name',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'LOGIN_SUCCESS' => [
                'label' => 'Login Success',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'LOGIN_FAILED' => [
                'label' => 'Login Failed',
                'class' => 'bg-red-50 text-red-700 border-red-200',
            ],
            'LOGIN_DEACTIVATED_BLOCKED' => [
                'label' => 'Deactivated Block',
                'class' => 'bg-rose-100 text-rose-800 border-rose-300',
            ],
            'USER_CREATED' => [
                'label' => 'User Created',
                'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            ],
            'USER_UPDATED' => [
                'label' => 'User Updated',
                'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            ],
            'USER_STATUS_TOGGLED' => [
                'label' => 'Status Toggled',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            'USER_DELETED' => [
                'label' => 'User Deleted',
                'class' => 'bg-red-100 text-red-800 border-red-300',
            ],
            'PASSWORD_CHANGED' => [
                'label' => 'Password Changed',
                'class' => 'bg-teal-50 text-teal-700 border-teal-200',
            ],
            'PASSWORD_RESET' => [
                'label' => 'Password Reset',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200',
            ],
            'LOGOUT' => [
                'label' => 'Logout',
                'class' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
            default => [
                'label' => str_replace('_', ' ', $this->action),
                'class' => 'bg-gray-50 text-gray-700 border-gray-200',
            ],
        };
    }
}
