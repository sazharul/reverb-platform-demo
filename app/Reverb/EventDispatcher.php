<?php

namespace Laravel\Reverb\Protocols\Pusher;

use App\Jobs\LogReverbEvent;
use App\Models\App as ReverbTenantApp;
use App\Models\Channel as TenantChannel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Application;
use Laravel\Reverb\Contracts\Connection;
use Laravel\Reverb\Protocols\Pusher\Contracts\ChannelManager;
use Laravel\Reverb\ServerProviderManager;
use Laravel\Reverb\Servers\Reverb\Contracts\PubSubProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Custom EventDispatcher — replaces vendor/laravel/reverb/src/Protocols/Pusher/EventDispatcher.php
 * via composer.json classmap override + exclude-from-classmap.
 *
 * Responsibilities:
 *  1. Enforce per-app channel permissions (block unregistered channels).
 *  2. Log every allowed/blocked event to the dedicated `reverb` log channel.
 *  3. Persist event logs to the DB via LogReverbEvent (synchronous, safe for ReactPHP).
 *  4. Forward the message to connected WebSocket clients (original Reverb behaviour).
 */
class EventDispatcher
{
    // Log channel used for all Reverb-related entries (storage/logs/reverb-YYYY-MM-DD.log)
    private const LOG_CHANNEL = 'reverb';

    // ──────────────────────────────────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Entry point — called by:
     *   • EventsController  (Pusher HTTP API, e.g. Laravel ShouldBroadcastNow)
     *   • ClientEvent::whisper() (WebSocket client-* events from browsers)
     *
     * @throws HttpException  403 when an API caller uses an unregistered channel.
     */
    public static function dispatch(Application $app, array $payload, ?Connection $connection = null): void
    {
        $source = $connection === null ? 'pusher_api' : 'websocket_client';
        $channels = Arr::wrap($payload['channels'] ?? $payload['channel'] ?? []);
        $eventName = $payload['event'] ?? 'unknown';

        Log::channel(self::LOG_CHANNEL)->debug('[EventDispatcher::dispatch] called', [
            'source'    => $source,
            'app_id'    => $app->id(),
            'event'     => $eventName,
            'channels'  => $channels,
            'socket_id' => $connection?->id(),
        ]);

        // ── 1. Channel permission gate ────────────────────────────────────────
        if (! static::channelsAreAllowed($app, $payload, $connection)) {
            // channelsAreAllowed already logged the block reason.
            // Return silently for WebSocket events (HttpException was thrown for API events).
            return;
        }

        // ── 2. Persist to DB (event log + Pulse) ─────────────────────────────
        static::logEvent($app, $payload, $connection);

        // ── 3. Forward to connected clients ──────────────────────────────────
        $server = app(ServerProviderManager::class);

        if ($server->shouldNotPublishEvents()) {
            Log::channel(self::LOG_CHANNEL)->debug('[EventDispatcher] dispatching synchronously (no scaling)', [
                'app_id'   => $app->id(),
                'event'    => $eventName,
                'channels' => $channels,
            ]);

            static::dispatchSynchronously($app, $payload, $connection);

            return;
        }

        Log::channel(self::LOG_CHANNEL)->debug('[EventDispatcher] publishing to PubSub (scaling enabled)', [
            'app_id'   => $app->id(),
            'event'    => $eventName,
            'channels' => $channels,
        ]);

        $data = [
            'type'        => 'message',
            'application' => serialize($app),
            'payload'     => $payload,
        ];

        if ($connection?->id() !== null) {
            $data['socket_id'] = $connection->id();
        }

        app(PubSubProvider::class)->publish($data);
    }

    /**
     * Broadcast the payload to every WebSocket connection subscribed to each channel.
     * Called directly when scaling is disabled, or via PubSubIncomingMessageHandler.
     */
    public static function dispatchSynchronously(Application $app, array $payload, ?Connection $connection = null): void
    {
        $channels = Arr::wrap($payload['channels'] ?? $payload['channel'] ?? []);

        foreach ($channels as $channel) {
            unset($payload['channels']);

            if (! $channel = app(ChannelManager::class)->for($app)->find($channel)) {
                continue;
            }

            $payload['channel'] = $channel->name();

            $channel->broadcast($payload, $connection);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Channel permission gate
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Decide whether all channels in the payload are permitted for this app.
     *
     * Rules:
     *  • Pusher protocol events (pusher:*, pusher_internal:*) → always allowed.
     *  • Plan with allow_any_channel = true                  → always allowed.
     *  • Otherwise: every channel must exist and be active in the channels table.
     *
     * On FAILURE:
     *  • API source (ShouldBroadcast / HTTP API):  throws HttpException(403).
     *  • WebSocket client event:                   returns false (silent drop).
     *
     * @throws HttpException
     */
    protected static function channelsAreAllowed(
        Application $app,
        array $payload,
        ?Connection $connection = null,
    ): bool {
        $eventName = $payload['event'] ?? '';
        $source    = $connection === null ? 'pusher_api' : 'websocket_client';

        // ── Pusher protocol messages — skip entirely ──────────────────────────
        if (str_starts_with($eventName, 'pusher:') || str_starts_with($eventName, 'pusher_internal:')) {
            Log::channel(self::LOG_CHANNEL)->debug('[ChannelGate] skipping Pusher protocol event', [
                'event'  => $eventName,
                'app_id' => $app->id(),
            ]);
            return true;
        }

        $channels = Arr::wrap($payload['channels'] ?? $payload['channel'] ?? []);
        if (empty($channels)) {
            Log::channel(self::LOG_CHANNEL)->debug('[ChannelGate] no channels in payload — allowed', [
                'event'  => $eventName,
                'app_id' => $app->id(),
            ]);
            return true;
        }

        try {
            // ── Resolve tenant app ────────────────────────────────────────────
            $tenantApp = ReverbTenantApp::where('app_id', $app->id())
                ->where('is_active', true)
                ->first();

            if (! $tenantApp) {
                Log::channel(self::LOG_CHANNEL)->warning('[ChannelGate] tenant app not found — letting through (auth will reject)', [
                    'reverb_app_id' => $app->id(),
                    'event'         => $eventName,
                ]);
                return true;
            }

            // ── Resolve owner + plan ──────────────────────────────────────────
            $owner = $tenantApp->user;
            if (! $owner) {
                Log::channel(self::LOG_CHANNEL)->warning('[ChannelGate] no owner for app — allowing (edge case)', [
                    'app_id' => $tenantApp->id,
                    'event'  => $eventName,
                ]);
                return true;
            }

            $plan = $owner->currentPlan();

            Log::channel(self::LOG_CHANNEL)->debug('[ChannelGate] plan resolved', [
                'app_id'           => $tenantApp->id,
                'app_name'         => $tenantApp->name,
                'owner_id'         => $owner->id,
                'plan_name'        => $plan->name,
                'allow_any_channel'=> (bool) $plan->allow_any_channel,
                'event'            => $eventName,
                'channels'         => $channels,
                'source'           => $source,
            ]);

            // ── Unlimited plan — bypass all channel checks ────────────────────
            if ($plan->allow_any_channel) {
                Log::channel(self::LOG_CHANNEL)->info('[ChannelGate] ALLOWED — plan permits any channel', [
                    'app_id'   => $tenantApp->id,
                    'plan'     => $plan->name,
                    'channels' => $channels,
                    'event'    => $eventName,
                ]);
                return true;
            }

            // ── Check every channel against the DB ────────────────────────────
            foreach ($channels as $rawChannel) {
                $channelName = is_string($rawChannel) ? $rawChannel : (string) $rawChannel;

                $registered = TenantChannel::where('app_id', $tenantApp->id)
                    ->where('name', $channelName)
                    ->where('is_active', true)
                    ->exists();

                if (! $registered) {
                    Log::channel(self::LOG_CHANNEL)->warning('[ChannelGate] BLOCKED — channel not registered', [
                        'app_id'   => $tenantApp->id,
                        'app_name' => $tenantApp->name,
                        'owner_id' => $owner->id,
                        'plan'     => $plan->name,
                        'channel'  => $channelName,
                        'event'    => $eventName,
                        'source'   => $source,
                    ]);

                    if ($connection === null) {
                        // HTTP API caller: return a proper 403 error response.
                        throw new HttpException(
                            403,
                            "Channel '{$channelName}' is not registered for this app. "
                            . "Create the channel in the portal first, or upgrade to an Unlimited plan."
                        );
                    }

                    // WebSocket client: silently drop.
                    return false;
                }

                Log::channel(self::LOG_CHANNEL)->info('[ChannelGate] ALLOWED — channel is registered', [
                    'app_id'  => $tenantApp->id,
                    'channel' => $channelName,
                    'event'   => $eventName,
                    'source'  => $source,
                ]);
            }
        } catch (HttpException $e) {
            // Re-throw so Reverb's HTTP server returns the 403 to the caller.
            throw $e;
        } catch (\Throwable $e) {
            // Log the error but never break event delivery.
            Log::channel(self::LOG_CHANNEL)->error('[ChannelGate] unexpected error — allowing event through', [
                'app_id'  => $app->id(),
                'event'   => $eventName,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  DB / Pulse logging
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Persist the event to event_logs + record in Laravel Pulse.
     *
     * Uses dispatchSync() because Reverb runs inside a ReactPHP event loop —
     * standard queue dispatch does NOT reliably persist from this async context.
     */
    protected static function logEvent(Application $app, array $payload, ?Connection $connection = null): void
    {
        try {
            $eventName = $payload['event'] ?? 'unknown';

            if (str_starts_with($eventName, 'pusher:') || str_starts_with($eventName, 'pusher_internal:')) {
                return;
            }

            $channels = Arr::wrap($payload['channels'] ?? $payload['channel'] ?? []);
            $data     = $payload['data'] ?? [];

            $parsedData = is_string($data)
                ? (json_decode($data, true) ?? ['_raw' => $data])
                : (is_array($data) ? $data : ['_raw' => (string) $data]);

            foreach ($channels as $channel) {
                LogReverbEvent::dispatchSync(
                    appReverbId: $app->id(),
                    channel:     is_string($channel) ? $channel : (string) $channel,
                    eventName:   $eventName,
                    payload:     $parsedData,
                    socketId:    $connection?->id(),
                );
            }
        } catch (\Throwable $e) {
            Log::channel(self::LOG_CHANNEL)->error('[EventDispatcher::logEvent] DB logging failed', [
                'app_id' => $app->id(),
                'error'  => $e->getMessage(),
            ]);
        }
    }
}

