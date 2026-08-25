<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'batch_status_history';

    protected $fillable = [
        'batch_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
