<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles {
        HasRoles::hasPermissionTo as permissionViaRole;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'is_active'         => 'boolean',
            'password'          => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'superadmin';
    }

    public function isAdminType(): bool
    {
        return in_array($this->user_type, ['superadmin', 'admin']);
    }

    public function isUser(): bool
    {
        return $this->user_type === 'user';
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->isSuperAdmin()) return true;
        return $this->permissionViaRole($permission, $guardName);
    }

    public function apps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\App::class);
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest('starts_at');
    }

    /**
     * Get the current plan or a default free-tier.
     */
    public function currentPlan(): SubscriptionPlan
    {
        if ($this->isSuperAdmin()) {
            $unlimited = SubscriptionPlan::freeTier();
            $unlimited->name = 'Unlimited (Admin)';
            $unlimited->max_apps = 999999;
            $unlimited->max_connections_per_app = 999999;
            $unlimited->daily_message_limit = 999999;
            $unlimited->max_channels_per_app = 999999;
            $unlimited->webhook_allowed = true;
            $unlimited->allow_any_channel = true;
            return $unlimited;
        }

        $sub = $this->activeSubscription;
        return $sub?->plan ?? SubscriptionPlan::freeTier();
    }
}
