<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\App as ReverbTenantApp;
use App\Models\Channel;
use App\Models\EventLog;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Pusher\Pusher;
use Throwable;

class EventTriggerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $appKey = (string) $request->header('X-App-Key', '');
        $signature = (string) $request->header('X-Signature', '');
        $rawPayload = $request->getContent();

        if ($appKey === '' || $signature === '') {
            return response()->json(['message' => 'Missing X-App-Key or X-Signature header.'], 422);
        }

        $tenantApp = ReverbTenantApp::query()
            ->where('app_key', $appKey)
            ->where('is_active', true)
            ->first();

        if (! $tenantApp) {
            return response()->json(['message' => 'Invalid app key.'], 401);
        }

        if (! $this->isValidSignature($tenantApp->revealSecret(), $rawPayload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        // Enforce daily message limit
        $limiter = app(PlanLimitService::class);
        $owner = $tenantApp->user;
        if ($owner && !$limiter->canSendMessage($owner)) {
            return response()->json(['message' => 'Daily message limit exceeded for your plan.'], 429);
        }

        $data = $request->validate([
            'channel' => 'required|string|max:200',
            'event_name' => 'required|string|max:200',
            'data' => 'required|array',
            'sender_socket_id' => 'nullable|string|max:100',
        ]);

        $normalizedChannel = $data['channel'];

        // ── Channel permission check ─────────────────────────────────────────
        // If the user's plan does NOT allow arbitrary channel names, verify that
        // this channel was created/registered in the portal first.
        $plan = $owner ? $owner->currentPlan() : null;
        $allowAnyChannel = $plan && $plan->allow_any_channel;

        if (! $allowAnyChannel) {
            $channelExists = Channel::where('app_id', $tenantApp->id)
                ->where('name', $normalizedChannel)
                ->where('is_active', true)
                ->exists();

            if (! $channelExists) {
                return response()->json([
                    'message' => 'Channel "' . $normalizedChannel . '" is not registered for this app. '
                        . 'Please create the channel in the portal first, or upgrade to an Unlimited plan.',
                ], 403);
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        // Accept both public and private channel names as-is.
        // Channel type is determined per-channel, not per-app.

        $eventLog = EventLog::create([
            'app_id' => $tenantApp->id,
            'uuid' => (string) Str::uuid(),
            'channel' => $normalizedChannel,
            'event_name' => $data['event_name'],
            'payload' => $data['data'],
            'sender_socket_id' => $data['sender_socket_id'] ?? null,
            'source' => 'api',
            'status' => 'pending',
        ]);

        try {
            $pusher = new Pusher(
                $tenantApp->app_key,
                $tenantApp->revealSecret(),
                $tenantApp->app_id,
                [
                    'host' => config('reverb.apps.apps.0.options.host', config('reverb.servers.reverb.hostname')),
                    'port' => (int) config('reverb.apps.apps.0.options.port', config('reverb.servers.reverb.port', 8080)),
                    'scheme' => config('reverb.apps.apps.0.options.scheme', 'http'),
                    'useTLS' => (bool) config('reverb.apps.apps.0.options.useTLS', false),
                ],
                null,
            );

            $pusher->trigger(
                channels: $normalizedChannel,
                event: $data['event_name'],
                data: $data['data'],
                params: array_filter([
                    'socket_id' => $data['sender_socket_id'] ?? null,
                ]),
            );

            $eventLog->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            return response()->json([
                'message' => 'Event delivered.',
                'event_uuid' => $eventLog->uuid,
            ], 202);
        } catch (Throwable $exception) {
            $eventLog->update([
                'status' => 'failed',
                'retry_count' => 1,
                'failure_reason' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Event delivery failed.',
                'event_uuid' => $eventLog->uuid,
            ], 500);
        }
    }

    private function isValidSignature(string $secret, string $payload, string $providedSignature): bool
    {
        $normalizedSignature = Str::startsWith($providedSignature, 'sha256=')
            ? Str::after($providedSignature, 'sha256=')
            : $providedSignature;

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $normalizedSignature);
    }

    private function normalizeChannelForApp(string $channel, string $mode): ?string
    {
        $isPrivateChannel = str_starts_with($channel, 'private-');

        if ($mode === 'private') {
            return $isPrivateChannel ? $channel : 'private-'.$channel;
        }

        return $isPrivateChannel ? null : $channel;
    }
}
