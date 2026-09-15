<?php

namespace App\Jobs;

use App\Models\App as ReverbTenantApp;
use App\Models\EventLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Pulse\Facades\Pulse;

class LogReverbEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public readonly string  $appReverbId,   // app_id string (from Reverb Application)
        public readonly string  $channel,
        public readonly string  $eventName,
        public readonly array   $payload,
        public readonly ?string $socketId = null,
    ) {}

    public function handle(): void
    {
        // Resolve tenant app by Reverb's string app_id
        $app = ReverbTenantApp::where('app_id', $this->appReverbId)->first();

        if (!$app) {
            return; // unknown app — skip silently
        }

        // Dedup: if an API-sourced log with the same channel + event was created in last 5 s, skip.
        $alreadyLoggedByApi = EventLog::where('app_id', $app->id)
            ->where('channel', $this->channel)
            ->where('event_name', $this->eventName)
            ->where('source', 'api')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($alreadyLoggedByApi) {
            return;
        }

        $source = str_starts_with($this->eventName, 'client-') ? 'websocket' : 'pusher_api';

        EventLog::create([
            'app_id'           => $app->id,
            'uuid'             => (string) Str::uuid(),
            'channel'          => $this->channel,
            'event_name'       => $this->eventName,
            'payload'          => $this->payload,
            'sender_socket_id' => $this->socketId,
            'source'           => $source,
            'status'           => 'delivered',
            'delivered_at'     => now(),
        ]);

        // ── Record in Laravel Pulse ──────────────────────────────────────────
        // Key encodes app + user so the Pulse card can filter and display both.
        $pulseKey = json_encode([
            'app_id'   => $app->id,
            'app_key'  => $app->app_key,
            'app_name' => $app->name,
            'user_id'  => $app->user_id,
        ]);

        Pulse::record(
            type: 'reverb_message',
            key: $pulseKey,
            value: 1,
        )->count();

        // Also record the channel for per-channel breakdown
        Pulse::record(
            type: 'reverb_channel',
            key: json_encode([
                'app_id'  => $app->id,
                'channel' => $this->channel,
                'user_id' => $app->user_id,
            ]),
            value: 1,
        )->count();

        // ── Plan Limit Check ─────────────────────────────────────────────────
        $this->checkAndEnforcePlanLimit($app);
    }

    /**
     * After every event, compare today's total against the subscription limit.
     * Sets a cache flag that the dashboard and AppManager can read.
     */
    private function checkAndEnforcePlanLimit(ReverbTenantApp $app): void
    {
        try {
            $user = $app->user;
            if (!$user) return;

            $plan      = $user->currentPlan();
            $limit     = (int) $plan->daily_message_limit;
            $cacheKey  = "reverb:daily_limit:{$app->id}:" . now()->format('Y-m-d');
            $ttl       = now()->endOfDay()->addHour();

            // Atomically count today's messages using cache
            $todayCount = (int) Cache::get($cacheKey, 0) + 1;
            Cache::put($cacheKey, $todayCount, $ttl);

            $limitFlagKey = "reverb:limit_exceeded:{$app->id}";

            if ($limit > 0 && $todayCount >= $limit) {
                // Mark app as over limit (expires tomorrow)
                Cache::put($limitFlagKey, true, now()->endOfDay()->addHour());

                Log::warning("Reverb daily message limit reached", [
                    'app_id'      => $app->id,
                    'app_name'    => $app->name,
                    'user_id'     => $app->user_id,
                    'today_count' => $todayCount,
                    'limit'       => $limit,
                ]);
            } else {
                // Under limit — clear any previous exceeded flag
                Cache::forget($limitFlagKey);
            }

            // Store latest usage snapshot in Pulse for the dashboard
            Pulse::set(
                type: 'reverb_daily_usage',
                key: (string) $app->id,
                value: json_encode([
                    'app_id'      => $app->id,
                    'app_name'    => $app->name,
                    'user_id'     => $app->user_id,
                    'today_count' => $todayCount,
                    'limit'       => $limit,
                    'pct'         => $limit > 0 ? round(($todayCount / $limit) * 100, 1) : 0,
                ]),
            );
        } catch (\Throwable $e) {
            // Never let a limit check break event delivery
            Log::debug('Plan limit check failed', ['error' => $e->getMessage()]);
        }
    }
}
