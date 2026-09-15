<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedEvent extends Model
{
    protected $fillable = [
        'event_log_id', 'failure_reason', 'retried_at', 'resolved_at', 'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'retried_at'  => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function eventLog(): BelongsTo
    {
        return $this->belongsTo(EventLog::class);
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }
}

