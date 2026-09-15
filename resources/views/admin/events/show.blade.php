@extends('layouts.dashboard')

@section('title', 'Event Detail')
@section('breadcrumb', 'Admin / Events / ' . $event->uuid)

@section('content')
    <div class="w-full space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Event Detail</h2>
                <p class="text-xs font-mono text-neutral-500 dark:text-neutral-400">{{ $event->uuid }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($event->status === 'failed')
                    <form action="{{ route('admin.events.retry', $event) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-primary">Retry Event</button>
                    </form>
                @endif
                <a href="{{ route('admin.events.index') }}" class="btn-secondary">← Back</a>
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Overview</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><p class="text-xs text-neutral-500 mb-1">Event Name</p><span class="font-mono text-sm">{{ $event->event_name }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">Channel</p><span class="font-mono text-sm">{{ $event->channel }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">App</p><span class="text-sm">{{ $event->app->name ?? '—' }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">Owner</p><span class="text-sm">{{ $event->app->user->name ?? '—' }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">Status</p>
                    @if($event->status === 'delivered')<span class="badge-green">Delivered</span>
                    @elseif($event->status === 'failed')<span class="badge-red">Failed</span>
                    @else<span class="badge-yellow">Pending</span>@endif
                </div>
                <div><p class="text-xs text-neutral-500 mb-1">Fired At</p><span class="text-sm">{{ $event->created_at->format('M d, Y H:i:s') }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">Delivered At</p><span class="text-sm">{{ $event->delivered_at?->format('M d, Y H:i:s') ?? '—' }}</span></div>
                <div><p class="text-xs text-neutral-500 mb-1">Retry Count</p><span class="text-sm">{{ $event->retry_count }}</span></div>
            </div>
        </div>

        @if($event->failure_reason)
            <div class="card p-5 border-red-200 dark:border-red-900/50">
                <h3 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-2">Failure Reason</h3>
                <pre class="code-block text-red-700 dark:text-red-300">{{ $event->failure_reason }}</pre>
            </div>
        @endif

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Payload</h3>
            <pre class="code-block"><code>{{ json_encode($event->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
        </div>
    </div>
@endsection

