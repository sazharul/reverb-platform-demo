<?php
// app/Models/EventLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EventLog extends Model
{
    protected $fillable = [
        'app_id', 'uuid', 'channel', 'event_name',
        'payload', 'sender_socket_id', 'source', 'status',
        'retry_count', 'failure_reason', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($model) => $model->uuid ??= Str::uuid());
    }

    public function app(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function scopeDelivered($query)  { return $query->where('status', 'delivered'); }
    public function scopeFailed($query)     { return $query->where('status', 'failed'); }
    public function scopePending($query)    { return $query->where('status', 'pending'); }
}