<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model
{
    protected $fillable = [
        'user_id', 'name', 'app_id', 'app_key',
        'app_secret', 'is_active', 'max_connections',
        'default_channel_type', 'allowed_origins', 'description',
        'webhook_url', 'rate_limit_per_minute',
    ];

    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'is_active'       => 'boolean',
        ];
    }

    protected $hidden = ['app_secret']; // never leak in JSON responses

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function eventLogs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AppToken::class);
    }

    public function appTokens(): HasMany
    {
        return $this->tokens();
    }

    public function connections(): HasMany
    {
        return $this->hasMany(AppConnection::class);
    }

    public function activeConnections(): HasMany
    {
        return $this->connections()->whereNull('disconnected_at');
    }

    public function failedEvents(): HasMany
    {
        return $this->hasMany(FailedEvent::class, 'event_log_id');
    }

    // Reveal secret only when explicitly needed
    public function revealSecret(): string
    {
        return $this->getRawOriginal('app_secret');
    }
}
