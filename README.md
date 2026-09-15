# PulseWire — Real-time Event Platform Demo

A self-hosted, multi-tenant **Laravel Reverb** platform demo — like Pusher, but on your own infrastructure. Manage Reverb apps, channels, signed event APIs, subscription plans, and event logs from a web dashboard.

> **Disclaimer:** This is a portfolio recreation for code review. It is not affiliated with any production deployment. Real-time chat on [pulsewire.demo](https://pulsewire.demo/) uses a similar Reverb-based architecture.

## Features

- Multi-tenant Reverb applications (database-driven `ApplicationProvider`)
- HMAC-signed `POST /api/v1/events` API for server-side broadcasts
- Channel registration and per-plan channel ACLs
- Subscription plans with daily message limits and connection caps
- Event log dashboard with delivery status
- Admin panel with Spatie role permissions
- Laravel Pulse integration for production monitoring (optional)
- `DEMO_MODE=true` — instant plan activation without SSLCommerz

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Real-time | Laravel Reverb (WebSockets) |
| UI | Livewire, Blade, Vite |
| Auth | Session + Sanctum API tokens |
| Permissions | Spatie Laravel Permission |
| Payments | SSLCommerz (bypassed in DEMO_MODE) |
| Monitoring | Laravel Pulse |
| Tests | Pest |

## Architecture

```
Backend API  →  POST /api/v1/events (HMAC signed)
                    ↓
              EventDispatcher (custom)
                    ↓
              Laravel Reverb  →  WebSocket clients
```

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for details.

## Quick Start (Docker)

```bash
git clone https://github.com/sazharul/reverb-platform-demo.git
cd reverb-platform-demo
docker compose up --build
```

| Service | URL |
|---------|-----|
| Dashboard | http://localhost:8000 |
| Reverb WebSocket | ws://localhost:8080 |

### Demo Accounts

See [docs/DEMO_ACCOUNTS.md](docs/DEMO_ACCOUNTS.md).

| Role | Email | Password |
|------|-------|----------|
| Super Admin | `admin@pulsewire.demo` | `DemoAdmin123!` |
| Demo User | `demo@pulsewire.demo` | `DemoUser123!` |

## Trigger an Event

The demo seeder creates an app with a known key. Sign the JSON body with HMAC-SHA256:

```php
$appKey = 'key123456789012345678901234567890';
$appSecret = 'demosecretdemosecretdemosecretdemosecretdemosecretdemosecretdemo12';

$payload = json_encode([
    'channel' => 'orders',
    'event_name' => 'product.added_to_cart',
    'data' => ['product_id' => 10, 'qty' => 1],
]);

$signature = hash_hmac('sha256', $payload, $appSecret);

Http::withHeaders([
    'X-App-Key' => $appKey,
    'X-Signature' => $signature,
])->withBody($payload, 'application/json')
  ->post('http://localhost:8000/api/v1/events');
```

## Local Development (without Docker)

```bash
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate --seed
php artisan serve          # terminal 1
php artisan reverb:start   # terminal 2
php artisan queue:work     # terminal 3
```

## License

MIT — see [LICENSE](LICENSE).
