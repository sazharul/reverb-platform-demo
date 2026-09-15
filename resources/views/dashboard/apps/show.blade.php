@extends('layouts.dashboard')

@section('title', 'App Details')
@section('breadcrumb', 'Dashboard / My Apps / '.$reverbApp->name)

@section('content')
    @php
        // Determine example channel from actual channels, fallback to public
        $exampleChannel = $reverbApp->channels->first();
        $hasPrivate = $reverbApp->channels->where('type', 'private')->count() > 0;
        $hasPresence = $reverbApp->channels->where('type', 'presence')->count() > 0;
    @endphp

    <div class="space-y-5 overflow-x-auto">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $reverbApp->name }}</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Created {{ $reverbApp->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('user.apps.edit', $reverbApp) }}" class="btn-primary">Edit App</a>
                <a href="{{ route('user.apps.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Back</a>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Credentials</h3>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">App ID</p>
                    <div class="flex items-center gap-2">
                        <code class="code-inline">{{ $reverbApp->app_id }}</code>
                        <button type="button" class="btn-secondary" onclick="copyValue('{{ $reverbApp->app_id }}')">Copy</button>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">App Key (safe for frontend)</p>
                    <div class="flex items-center gap-2">
                        <code class="code-inline">{{ $reverbApp->app_key }}</code>
                        <button type="button" class="btn-secondary" onclick="copyValue('{{ $reverbApp->app_key }}')">Copy</button>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">App Secret (backend only)</p>
                    <div class="flex items-center gap-2">
                        <code id="app-secret" data-secret="{{ $reverbApp->revealSecret() }}" class="code-inline">****************************************************************</code>
                        <button type="button" class="btn-secondary" onclick="toggleSecret()">Reveal</button>
                        <button type="button" class="btn-secondary" onclick="copySecret()">Copy</button>
                    </div>
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">Never expose this secret in browser code.</p>
                </div>
            </div>
        </div>

        {{-- ── Channels ────────────────────────────────────────── --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Channels ({{ $reverbApp->channels->count() }})</h3>
                <a href="{{ route('user.channels.create') }}?app_id={{ $reverbApp->id }}" class="text-xs text-brand-600 hover:underline">+ Add Channel</a>
            </div>

            @if($reverbApp->channels->isEmpty())
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No channels created yet. <a href="{{ route('user.channels.create') }}?app_id={{ $reverbApp->id }}" class="text-brand-600 hover:underline">Create one</a>.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table text-sm">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($reverbApp->channels as $channel)
                            <tr>
                                <td><code class="font-mono text-xs">{{ $channel->name }}</code></td>
                                <td>
                                    @if($channel->type === 'private')
                                        <span class="badge-yellow">Private</span>
                                    @elseif($channel->type === 'presence')
                                        <span class="badge-purple">Presence</span>
                                    @else
                                        <span class="badge-green">Public</span>
                                    @endif
                                </td>
                                <td>
                                    @if($channel->is_active)
                                        <span class="badge-green">Active</span>
                                    @else
                                        <span class="badge-neutral">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ── Public Channel Example ───────────────────────────── --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Public Channel — WebSocket Example</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-3">Subscribe to a <strong>public</strong> channel in your frontend.</p>

<pre class="code-block"><code>import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'reverb',
  key: '{{ $reverbApp->app_key }}',
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT || 80),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT || 443),
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
  enabledTransports: ['ws', 'wss'],
});

// Public channel — no auth needed
echo.channel('orders').listen('.order.placed', (payload) => {
  console.log('Received public event:', payload);
});</code></pre>
        </div>

        {{-- ── Private Channel Example ──────────────────────────── --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Private Channel — WebSocket Example</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-3">Subscribe to a <strong>private</strong> channel (requires server-side auth endpoint).</p>

<pre class="code-block"><code>// Private channel — requires auth endpoint
echo.private('orders').listen('.order.placed', (payload) => {
  console.log('Received private event:', payload);
});</code></pre>
        </div>

        {{-- ── Backend Trigger Example ──────────────────────────── --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Backend Event Trigger Example</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-300 mb-3">Use your app secret for HMAC signing from backend only.</p>
<pre class="code-block"><code>$payload = json_encode([
    'channel' => 'orders',          // or 'private-orders' for private
    'event_name' => 'order.placed',
    'data' => ['order_id' => 42, 'total' => 1599],
]);

$signature = hash_hmac('sha256', $payload, '{{ $reverbApp->revealSecret() }}');

Http::withHeaders([
    'X-App-Key' => '{{ $reverbApp->app_key }}',
    'X-Signature' => $signature,
])->post(url('/api/v1/events'), json_decode($payload, true));</code></pre>
        </div>
    </div>

    <script>
        function copyValue(value) {
            navigator.clipboard.writeText(value);
        }

        function toggleSecret() {
            const el = document.getElementById('app-secret');
            const isMasked = el.textContent.includes('*');
            el.textContent = isMasked ? el.dataset.secret : '****************************************************************';
        }

        function copySecret() {
            const el = document.getElementById('app-secret');
            navigator.clipboard.writeText(el.dataset.secret);
        }
    </script>
@endsection

