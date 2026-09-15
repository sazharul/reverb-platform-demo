<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AppToken extends Model
{
    protected $fillable = [
        'app_id', 'name', 'token_hash', 'is_active', 'last_used_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'last_used_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public $timestamps = false;

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public static function generate(int $appId, string $name): array
    {
        $plainToken = Str::random(64);

        $token = static::create([
            'app_id'     => $appId,
            'name'       => $name,
            'token_hash' => hash('sha256', $plainToken),
            'is_active'  => true,
        ]);

        return ['token' => $token, 'plain' => $plainToken];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
