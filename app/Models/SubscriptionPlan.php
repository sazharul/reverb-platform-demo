<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price', 'billing_cycle',
        'max_apps', 'max_connections_per_app', 'daily_message_limit',
        'max_channels_per_app', 'webhook_allowed', 'allow_any_channel',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price'             => 'decimal:2',
            'webhook_allowed'   => 'boolean',
            'allow_any_channel' => 'boolean',
            'is_active'         => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($plan) {
            $plan->slug ??= Str::slug($plan->name);
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'subscription_plan_id');
    }

    public function activeSubscribers(): HasMany
    {
        return $this->subscriptions()->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isFree(): bool
    {
        return $this->price <= 0;
    }

    /**
     * Returns a default free-tier plan object (not persisted).
     */
    public static function freeTier(): static
    {
        return new static([
            'name'                    => 'Free',
            'slug'                    => 'free',
            'price'                   => 0,
            'billing_cycle'           => 'monthly',
            'max_apps'                => 3,
            'max_connections_per_app' => 50,
            'daily_message_limit'     => 5000,
            'max_channels_per_app'    => 20,
            'webhook_allowed'         => false,
            'allow_any_channel'       => false,
            'is_active'               => true,
        ]);
    }
}

