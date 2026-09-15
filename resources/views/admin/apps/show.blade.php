@extends('layouts.dashboard')

@section('title', $app->name)
@section('breadcrumb', 'Admin / Apps / ' . $app->name)

@section('content')
    <div class="w-full space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $app->name }}</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Owned by {{ $app->user->name ?? '—' }} · Created {{ $app->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.apps.edit', $app) }}" class="btn-primary">Edit</a>
                <a href="{{ route('admin.apps.index') }}" class="btn-secondary">Back</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="stat-card"><div><p class="text-xs text-neutral-500 font-medium">Status</p>@if($app->is_active)<span class="badge-green">Active</span>@else<span class="badge-neutral">Inactive</span>@endif</div></div>
            <div class="stat-card"><div><p class="text-xs text-neutral-500 font-medium">Events</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($app->event_logs_count) }}</p></div></div>
            <div class="stat-card"><div><p class="text-xs text-neutral-500 font-medium">Channels</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ $app->channels_count }}</p></div></div>
            <div class="stat-card"><div><p class="text-xs text-neutral-500 font-medium">Max Connections</p><p class="text-xl font-bold text-neutral-900 dark:text-white">{{ number_format($app->max_connections) }}</p></div></div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3">Credentials</h3>
            <div class="space-y-2 text-sm">
                <div><span class="text-neutral-500 dark:text-neutral-400">App ID:</span> <code class="font-mono text-neutral-800 dark:text-neutral-200">{{ $app->app_id }}</code></div>
                <div><span class="text-neutral-500 dark:text-neutral-400">App Key:</span> <code class="font-mono text-neutral-800 dark:text-neutral-200">{{ $app->app_key }}</code></div>
                @if($app->webhook_url)<div><span class="text-neutral-500 dark:text-neutral-400">Webhook URL:</span> <code class="font-mono text-xs">{{ $app->webhook_url }}</code></div>@endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-header"><h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Events</h3></div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Event</th><th>Channel</th><th>Status</th><th>Time</th></tr></thead>
                    <tbody>
                    @forelse($recentEvents as $event)
                        <tr>
                            <td><span class="font-mono text-xs">{{ $event->event_name }}</span></td>
                            <td><span class="font-mono text-xs text-neutral-500">{{ $event->channel }}</span></td>
                            <td>@if($event->status === 'delivered')<span class="badge-green">Delivered</span>@elseif($event->status === 'failed')<span class="badge-red">Failed</span>@else<span class="badge-yellow">Pending</span>@endif</td>
                            <td class="text-xs text-neutral-400">{{ $event->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-6 text-neutral-400 text-xs">No events yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

