<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'late_minutes',
        'status',
        'ip_address',
        'notes',
        'is_forgiven',
        'forgiven_by',
        'forgive_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_forgiven' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forgiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forgiven_by');
    }
}
