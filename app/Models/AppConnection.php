<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppConnection extends Model
{
    protected $fillable = [
        'app_id', 'socket_id', 'ip_address', 'origin',
        'connected_at', 'disconnected_at',
    ];

    protected function casts(): array
    {
        return [
            'connected_at'    => 'datetime',
            'disconnected_at' => 'datetime',
        ];
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('disconnected_at');
    }
}

