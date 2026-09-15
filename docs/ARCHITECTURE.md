# Architecture

## Overview

PulseWire is a multi-tenant wrapper around Laravel Reverb. Each registered user can create Reverb "apps" with unique credentials. Backend services trigger broadcasts via a signed HTTP API; browsers connect via WebSockets.

## Request Flow

```
Client backend
    → POST /api/v1/events
        Headers: X-App-Key, X-Signature (HMAC-SHA256 of raw body)
    → EventTriggerController validates signature + plan limits
    → Pusher HTTP API → Reverb server
    → WebSocket subscribers receive event
    → EventDispatcher logs to DB (LogReverbEvent job)
```

## Multi-tenant Reverb

[`app/Reverb/ReverbAppManager.php`](../app/Reverb/ReverbAppManager.php) implements Laravel's `ApplicationProvider` interface. Reverb apps are stored in the `apps` table and cached for 5 minutes.

## Custom EventDispatcher

[`app/Reverb/EventDispatcher.php`](../app/Reverb/EventDispatcher.php) replaces the vendor class via composer classmap override. It adds:

1. Channel permission enforcement (registered channels only on limited plans)
2. Structured logging to `storage/logs/reverb-*.log`
3. Async persistence to `event_logs` table

## Plan Limits

[`app/Services/PlanLimitService.php`](../app/Services/PlanLimitService.php) enforces:

- Max apps per user
- Max connections per app
- Daily message quota
- Max channels per app
- Webhook availability (paid plans)

## DEMO_MODE

When `DEMO_MODE=true`:

- Paid plan checkout activates instantly without SSLCommerz
- Free plan `subscribe-free` works as normal
- No live payment gateway credentials required

## Production Operations

In production, run Reverb and Pulse under Supervisor:

```bash
sudo supervisorctl restart pulsewire-reverb
sudo supervisorctl restart pulsewire-pulse
```

Pulse is optional in the Docker demo to keep startup lightweight.
