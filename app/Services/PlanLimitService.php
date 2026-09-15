<?php

namespace App\Services;

use App\Models\App as ReverbApp;
use App\Models\EventLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Pulse\Facades\Pulse;

class PlanLimitService
{
    public function getPlan(User $user): SubscriptionPlan
    {
        return $user->currentPlan();
    }

    public function canCreateApp(User $user): bool
    {
        return $user->apps()->count() < $this->getPlan($user)->max_apps;
    }

    public function canCreateChannel(User $user, int $appId): bool
    {
        return \App\Models\Channel::where('app_id', $appId)->count()
            < $this->getPlan($user)->max_channels_per_app;
    }

    public function canSendMessage(User $user): bool
    {
        return $this->todayMessageCount($user) < $this->getPlan($user)->daily_message_limit;
    }

    // ── Counting ─────────────────────────────────────────────────────────────

    /**
     * Today's message count from EventLog (authoritative).
     */
    public function todayMessageCount(User $user): int
    {
        return EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Fast today count from the atomic cache counter set by LogReverbEvent.
     * Falls back to EventLog if cache is cold.
     */
    public function todayMessageCountFast(User $user): int
    {
        $appIds = ReverbApp::where('user_id', $user->id)->pluck('id');
        $total  = 0;

        foreach ($appIds as $appId) {
            $cacheKey = "reverb:daily_limit:{$appId}:" . now()->format('Y-m-d');
            $total += (int) Cache::get($cacheKey, 0);
        }

        // If cache is cold (e.g. server restart) fall back to DB
        return $total > 0 ? $total : $this->todayMessageCount($user);
    }

    /**
     * Per-app today count from cache.
     */
    public function todayMessageCountForApp(int $appId): int
    {
        $cacheKey = "reverb:daily_limit:{$appId}:" . now()->format('Y-m-d');
        return (int) Cache::get($cacheKey, 0);
    }

    /**
     * Whether a specific app has exceeded its user's daily limit.
     */
    public function isAppLimitExceeded(int $appId): bool
    {
        return (bool) Cache::get("reverb:limit_exceeded:{$appId}", false);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function maxConnectionsForApp(User $user): int
    {
        return $this->getPlan($user)->max_connections_per_app;
    }

    public function isWebhookAllowed(User $user): bool
    {
        return $this->getPlan($user)->webhook_allowed;
    }

    public function canUseAnyChannel(User $user): bool
    {
        return (bool) $this->getPlan($user)->allow_any_channel;
    }

    // ── Summary for dashboard ─────────────────────────────────────────────────

    public function usageSummary(User $user): array
    {
        $plan          = $this->getPlan($user);
        $messagesToday = $this->todayMessageCountFast($user);
        $limit         = (int) $plan->daily_message_limit;
        $usagePct      = $limit > 0 ? min(round(($messagesToday / $limit) * 100, 1), 100) : 0;

        return [
            'plan_name'              => $plan->name,
            'apps_used'              => $user->apps()->count(),
            'apps_limit'             => $plan->max_apps,
            'messages_today'         => $messagesToday,
            'messages_daily_limit'   => $limit,
            'usage_pct'              => $usagePct,
            'limit_warning'          => $usagePct >= 90,
            'limit_exceeded'         => $usagePct >= 100,
            'max_connections'        => $plan->max_connections_per_app,
            'max_channels'           => $plan->max_channels_per_app,
            'webhook_allowed'        => $plan->webhook_allowed,
        ];
    }

    /**
     * Per-app usage breakdown for the stats page.
     */
    public function perAppUsageSummary(User $user): array
    {
        $plan = $this->getPlan($user);
        $limit = (int) $plan->daily_message_limit;

        return ReverbApp::where('user_id', $user->id)
            ->get()
            ->map(function ($app) use ($limit) {
                $count = $this->todayMessageCountForApp($app->id);
                $pct   = $limit > 0 ? min(round(($count / $limit) * 100, 1), 100) : 0;

                return [
                    'app_id'    => $app->id,
                    'app_name'  => $app->name,
                    'app_key'   => $app->app_key,
                    'today'     => $count,
                    'limit'     => $limit,
                    'pct'       => $pct,
                    'exceeded'  => $this->isAppLimitExceeded($app->id),
                    'warning'   => $pct >= 90,
                ];
            })
            ->toArray();
    }
}

