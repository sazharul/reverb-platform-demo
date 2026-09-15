<?php

namespace App\Listeners;

use App\Jobs\LogReverbEvent;
use Illuminate\Support\Facades\Log;
use Laravel\Reverb\Events\MessageReceived;

class ReverbMessageListener
{
    /**
     * Intercept every WebSocket message passing through Reverb.
     * Filter for actual data events (skip Pusher protocol messages)
     * and execute a synchronous job to log them in the database.
     *
     * dispatchSync is used because Reverb runs inside a ReactPHP event loop
     * where standard queue dispatch does NOT reliably persist.
     */
    public function handle(MessageReceived $event): void
    {
        try {
            $decoded = json_decode($event->message, true);

            if (!is_array($decoded) || empty($decoded['event'])) {
                return;
            }

            $eventName = $decoded['event'];

            // Skip all Pusher protocol messages (subscribe, ping, pong, etc.)
            if (str_starts_with($eventName, 'pusher:') || str_starts_with($eventName, 'pusher_internal:')) {
                return;
            }

            // This is a real data event (client-* event or server-triggered event)
            $channel = $decoded['channel'] ?? 'unknown';
            $rawData = $decoded['data'] ?? [];

            // data might be JSON string or array
            $payload = is_string($rawData)
                ? (json_decode($rawData, true) ?? ['_raw' => $rawData])
                : $rawData;

            // Get the app ID and socket ID from the Reverb connection
            $appReverbId = $event->connection->app()->id();
            $socketId    = $event->connection->id();

            LogReverbEvent::dispatchSync(
                appReverbId: $appReverbId,
                channel: $channel,
                eventName: $eventName,
                payload: $payload,
                socketId: $socketId,
            );
        } catch (\Throwable $e) {
            Log::warning('ReverbMessageListener failed', [
                'error'   => $e->getMessage(),
                'message' => substr($event->message ?? '', 0, 500),
            ]);
        }
    }
}

