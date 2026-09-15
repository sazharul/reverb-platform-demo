@extends('layouts.dashboard')

@section('title', 'Documentation')
@section('breadcrumb', 'Dashboard / Documentation')

@section('content')
    <div class="w-full prose prose-sm dark:prose-invert prose-headings:text-neutral-900 dark:prose-headings:text-white prose-a:text-brand-600 dark:prose-a:text-brand-400 max-w-none">

        <h1 class="text-2xl font-bold">Developer Documentation</h1>
        <p class="lead text-neutral-600 dark:text-neutral-300">Everything you need to integrate real-time WebSocket events into your application.</p>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Quick Start ──────────────────────────────────────── --}}
        <h2 id="quick-start">🚀 Quick Start</h2>
        <ol>
            <li><strong>Create an App</strong> — Go to <a href="{{ route('user.apps.create') }}">My Apps → Create App</a>. Choose public or private channel mode.</li>
            <li><strong>Copy Credentials</strong> — You'll receive an <code>app_id</code>, <code>app_key</code>, and <code>app_secret</code>.</li>
            <li><strong>Install Client Libraries</strong> — Use <code>pusher-js</code> + <code>laravel-echo</code> for frontend, and the Pusher PHP SDK (or any HTTP client with HMAC) for backend.</li>
            <li><strong>Connect &amp; Listen</strong> — Subscribe to channels and listen for events in real-time.</li>
        </ol>

<pre class="code-block"><code># Frontend (npm)
npm install pusher-js laravel-echo

# Backend (composer)
composer require pusher/pusher-php-server</code></pre>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Frontend Setup ───────────────────────────────────── --}}
        <h2 id="frontend-setup">📡 Frontend — Listening to Events</h2>
        <p>Configure Laravel Echo in your frontend JavaScript:</p>

<pre class="code-block"><code>import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'reverb',
    key: 'YOUR_APP_KEY',
    wsHost: 'your-server-domain.com',
    wsPort: 80,
    wssPort: 443,
    forceTLS: true,
    enabledTransports: ['ws', 'wss'],
});

// Public channel
echo.channel('orders')
    .listen('.order.placed', (e) => {
        console.log('New order:', e);
    });

// Private channel (requires auth endpoint)
echo.private('orders')
    .listen('.order.placed', (e) => {
        console.log('Private order event:', e);
    });</code></pre>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900/40 rounded-xl p-4 my-4">
            <p class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">⚠️ Important</p>
            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">Event names must be prefixed with a dot (<code>.event.name</code>) in Echo's <code>.listen()</code> method.</p>
        </div>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Backend Trigger ──────────────────────────────────── --}}
        <h2 id="backend-trigger">⚡ Backend — Triggering Events</h2>
        <p>Fire events from your backend using our HTTP API with HMAC-SHA256 authentication.</p>

        <h3>Endpoint</h3>
<pre class="code-block"><code>POST {{ url('/api/v1/events') }}

Headers:
  X-App-Key: YOUR_APP_KEY
  X-Signature: HMAC-SHA256(raw_json_body, YOUR_APP_SECRET)
  Content-Type: application/json

Body:
{
    "channel": "orders",
    "event_name": "order.placed",
    "data": {
        "order_id": 123,
        "total": 49.99
    }
}</code></pre>

        <h3>PHP / Laravel Example</h3>
<pre class="code-block"><code>use Illuminate\Support\Facades\Http;

$payload = json_encode([
    'channel'    => 'orders',
    'event_name' => 'order.placed',
    'data'       => ['order_id' => 123, 'total' => 49.99],
]);

$signature = hash_hmac('sha256', $payload, env('REVERB_APP_SECRET'));

$response = Http::withHeaders([
    'X-App-Key'   => env('REVERB_APP_KEY'),
    'X-Signature'  => $signature,
    'Content-Type' => 'application/json',
])->withBody($payload, 'application/json')
  ->post('{{ url('/api/v1/events') }}');

// Response: { "message": "Event delivered.", "event_uuid": "..." }</code></pre>

        <h3>Node.js Example</h3>
<pre class="code-block"><code>const crypto = require('crypto');
const axios = require('axios');

const payload = JSON.stringify({
    channel: 'orders',
    event_name: 'order.placed',
    data: { order_id: 123, total: 49.99 }
});

const signature = crypto
    .createHmac('sha256', process.env.APP_SECRET)
    .update(payload)
    .digest('hex');

await axios.post('{{ url('/api/v1/events') }}', payload, {
    headers: {
        'X-App-Key': process.env.APP_KEY,
        'X-Signature': signature,
        'Content-Type': 'application/json'
    }
});</code></pre>

        <h3>cURL Example</h3>
<pre class="code-block"><code>PAYLOAD='{"channel":"orders","event_name":"order.placed","data":{"order_id":123}}'
SECRET="YOUR_APP_SECRET"
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')

curl -X POST {{ url('/api/v1/events') }} \
  -H "Content-Type: application/json" \
  -H "X-App-Key: YOUR_APP_KEY" \
  -H "X-Signature: $SIGNATURE" \
  -d "$PAYLOAD"</code></pre>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Using Pusher PHP SDK ─────────────────────────────── --}}
        <h2 id="pusher-sdk">🔧 Using Pusher PHP SDK Directly</h2>
        <p>Since this server is Pusher-compatible, you can use the official Pusher SDK:</p>

<pre class="code-block"><code>use Pusher\Pusher;

$pusher = new Pusher(
    'YOUR_APP_KEY',
    'YOUR_APP_SECRET',
    'YOUR_APP_ID',
    [
        'host'   => 'your-server-domain.com',
        'port'   => 443,
        'scheme' => 'https',
        'useTLS' => true,
    ]
);

$pusher->trigger('orders', 'order.placed', [
    'order_id' => 123,
    'total'    => 49.99,
]);</code></pre>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Channel Types ────────────────────────────────────── --}}
        <h2 id="channel-types">📺 Channel Types</h2>

        <table class="table-auto w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left px-3 py-2">Type</th>
                    <th class="text-left px-3 py-2">Prefix</th>
                    <th class="text-left px-3 py-2">Auth Required</th>
                    <th class="text-left px-3 py-2">Use Case</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-3 py-2"><span class="badge-green">Public</span></td>
                    <td class="px-3 py-2 font-mono">—</td>
                    <td class="px-3 py-2">No</td>
                    <td class="px-3 py-2">Notifications, public updates, dashboards</td>
                </tr>
                <tr>
                    <td class="px-3 py-2"><span class="badge-yellow">Private</span></td>
                    <td class="px-3 py-2 font-mono">private-</td>
                    <td class="px-3 py-2">Yes</td>
                    <td class="px-3 py-2">User-specific data, orders, messages</td>
                </tr>
                <tr>
                    <td class="px-3 py-2"><span class="badge-purple">Presence</span></td>
                    <td class="px-3 py-2 font-mono">presence-</td>
                    <td class="px-3 py-2">Yes</td>
                    <td class="px-3 py-2">Who's online, collaborative features</td>
                </tr>
            </tbody>
        </table>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Private Channel Auth ─────────────────────────────── --}}
        <h2 id="private-auth">🔐 Private Channel Authentication</h2>
        <p>For private/presence channels, the client (pusher-js) will call your auth endpoint. Configure Echo:</p>

<pre class="code-block"><code>const echo = new Echo({
    broadcaster: 'reverb',
    key: 'YOUR_APP_KEY',
    // ...
    authEndpoint: '{{ url('/api/v1/reverb/auth') }}',
    auth: {
        headers: {
            Authorization: 'Bearer YOUR_SANCTUM_TOKEN',
        },
        params: {
            app_key: 'YOUR_APP_KEY',
        }
    },
});</code></pre>

        <p>The auth endpoint validates ownership and returns an HMAC signature for the channel subscription.</p>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── API Reference ────────────────────────────────────── --}}
        <h2 id="api-reference">📖 API Reference</h2>

        <h3>POST <code>/api/v1/events</code></h3>
        <p>Trigger an event on a channel.</p>
        <table class="table-auto w-full text-sm">
            <thead><tr><th class="text-left px-3 py-2">Field</th><th class="text-left px-3 py-2">Type</th><th class="text-left px-3 py-2">Required</th><th class="text-left px-3 py-2">Description</th></tr></thead>
            <tbody>
                <tr><td class="px-3 py-2 font-mono">channel</td><td class="px-3 py-2">string</td><td class="px-3 py-2">Yes</td><td class="px-3 py-2">Channel name (e.g. <code>orders</code> or <code>private-orders</code>)</td></tr>
                <tr><td class="px-3 py-2 font-mono">event_name</td><td class="px-3 py-2">string</td><td class="px-3 py-2">Yes</td><td class="px-3 py-2">Event name (e.g. <code>order.placed</code>)</td></tr>
                <tr><td class="px-3 py-2 font-mono">data</td><td class="px-3 py-2">object</td><td class="px-3 py-2">Yes</td><td class="px-3 py-2">Event payload</td></tr>
                <tr><td class="px-3 py-2 font-mono">sender_socket_id</td><td class="px-3 py-2">string</td><td class="px-3 py-2">No</td><td class="px-3 py-2">Exclude sender from receiving</td></tr>
            </tbody>
        </table>

        <h4>Response (202 Accepted)</h4>
<pre class="code-block"><code>{
    "message": "Event delivered.",
    "event_uuid": "550e8400-e29b-41d4-a716-446655440000"
}</code></pre>

        <h4>Error Responses</h4>
        <table class="table-auto w-full text-sm">
            <thead><tr><th class="text-left px-3 py-2">Code</th><th class="text-left px-3 py-2">Meaning</th></tr></thead>
            <tbody>
                <tr><td class="px-3 py-2">401</td><td class="px-3 py-2">Invalid app key or HMAC signature</td></tr>
                <tr><td class="px-3 py-2">422</td><td class="px-3 py-2">Validation error or channel mode mismatch</td></tr>
                <tr><td class="px-3 py-2">429</td><td class="px-3 py-2">Daily message limit exceeded</td></tr>
                <tr><td class="px-3 py-2">500</td><td class="px-3 py-2">Internal delivery failure (event logged as failed)</td></tr>
            </tbody>
        </table>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Webhook Configuration ────────────────────────────── --}}
        <h2 id="webhooks">🔔 Webhooks</h2>
        <p>If your plan supports webhooks, you can set a webhook URL on your app. When an event is successfully delivered, a POST request is sent to your URL:</p>

<pre class="code-block"><code>POST https://your-server.com/webhook

Headers:
  X-Webhook-Signature: HMAC-SHA256(body, YOUR_APP_SECRET)
  Content-Type: application/json

Body:
{
    "event_uuid": "...",
    "channel": "orders",
    "event_name": "order.placed",
    "data": { ... },
    "delivered_at": "2026-03-29T10:30:00Z"
}</code></pre>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Rate Limits ──────────────────────────────────────── --}}
        <h2 id="rate-limits">⏱️ Rate Limits & Quotas</h2>
        <p>Limits depend on your subscription plan:</p>
        <ul>
            <li><strong>Daily Messages</strong> — Number of events you can trigger per day across all apps.</li>
            <li><strong>Max Connections</strong> — Concurrent WebSocket connections per app.</li>
            <li><strong>Max Channels</strong> — Number of channels you can create per app.</li>
            <li><strong>Max Apps</strong> — Total apps you can create.</li>
        </ul>
        <p>Check your current usage in <a href="{{ route('user.stats') }}">Usage Stats</a> or <a href="{{ route('user.settings') }}">Settings</a>.</p>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        {{-- ── Environment Variables ────────────────────────────── --}}
        <h2 id="env-vars">🗝️ Environment Variables (Client App)</h2>
        <p>Add these to your client application's <code>.env</code>:</p>

<pre class="code-block"><code>REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_APP_ID=your_app_id
REVERB_HOST=your-server-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# For Vite-based frontends
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"</code></pre>

        <hr class="my-6 border-neutral-200 dark:border-neutral-800">

        <h2 id="support">💬 Support</h2>
        <p>Need help? Contact the platform administrator or check the <a href="{{ route('user.plans.index') }}">pricing plans</a> for priority support options.</p>

    </div>
@endsection

