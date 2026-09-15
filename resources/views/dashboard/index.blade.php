{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Overview')

@section('content')

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <div class="stat-card">
            <div class="stat-icon bg-brand-50 dark:bg-brand-900/20">
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Total Apps</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ $stats['total_apps'] }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">{{ $stats['active_apps'] }} active</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green-50 dark:bg-green-900/20">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Delivered Today</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['delivered_today']) }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">{{ number_format($stats['total_events']) }} total</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-yellow-50 dark:bg-yellow-900/20">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Pending</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['pending_events']) }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">In queue</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-red-50 dark:bg-red-900/20">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Failed Events</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['failed_events']) }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">
                    @if($stats['failed_events'] > 0)
                        <a href="{{ route('user.events.index') }}?status=failed"
                           class="text-red-500 hover:underline">View all</a>
                    @else
                        All clear
                    @endif
                </p>
            </div>
        </div>

    </div>

    {{-- Plan usage bar --}}
    <div class="card p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Plan: {{ $usage['plan_name'] }}</h3>
            <a href="{{ route('user.plans.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Upgrade</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-neutral-500">Apps</span>
                    <span class="font-medium">{{ $usage['apps_used'] }} / {{ $usage['apps_limit'] >= 999999 ? '∞' : $usage['apps_limit'] }}</span>
                </div>
                <div class="progress-bar"><div class="progress-bar-fill bg-brand-600" style="width: {{ $usage['apps_limit'] >= 999999 ? 5 : min(100, ($usage['apps_used'] / max(1, $usage['apps_limit'])) * 100) }}%"></div></div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-neutral-500">Messages Today</span>
                    <span class="font-medium">{{ number_format($usage['messages_today']) }} / {{ $usage['messages_daily_limit'] >= 999999 ? '∞' : number_format($usage['messages_daily_limit']) }}</span>
                </div>
                <div class="progress-bar"><div class="progress-bar-fill bg-green-500" style="width: {{ $usage['messages_daily_limit'] >= 999999 ? 2 : min(100, ($usage['messages_today'] / max(1, $usage['messages_daily_limit'])) * 100) }}%"></div></div>
            </div>
            <div class="text-xs"><span class="text-neutral-500">Max Conn/App:</span> <span class="font-medium">{{ $usage['max_connections'] >= 999999 ? '∞' : number_format($usage['max_connections']) }}</span></div>
            <div class="text-xs"><span class="text-neutral-500">Webhooks:</span> {!! $usage['webhook_allowed'] ? '<span class="text-green-600 font-medium">Enabled</span>' : '<span class="text-neutral-400">Not included</span>' !!}</div>
        </div>
    </div>

    {{-- Two column layout --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Recent events --}}
        <div class="xl:col-span-2 card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Events</h2>
                <a href="{{ route('user.events.index') }}"
                   class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">
                    View all
                </a>
            </div>
            <div class="overflow-x-auto">
                @if($recentEvents->isEmpty())
                    <div class="px-6 py-10 text-center">
                        <svg class="w-8 h-8 text-neutral-300 dark:text-neutral-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No events yet</p>
                        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
                            Create an app and start firing events
                        </p>
                    </div>
                @else
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Event</th>
                            <th>Channel</th>
                            <th>App</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recentEvents as $event)
                            <tr>
                                <td>
                                <span class="font-mono text-xs text-neutral-700 dark:text-neutral-300">
                                    {{ $event->event_name }}
                                </span>
                                </td>
                                <td>
                                <span class="font-mono text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $event->channel }}
                                </span>
                                </td>
                                <td class="text-xs">{{ $event->app->name ?? '—' }}</td>
                                <td>
                                    @if($event->status === 'delivered')
                                        <span class="badge-green">Delivered</span>
                                    @elseif($event->status === 'failed')
                                        <span class="badge-red">Failed</span>
                                    @else
                                        <span class="badge-yellow">Pending</span>
                                    @endif
                                </td>
                                <td class="text-xs text-neutral-400 dark:text-neutral-500 whitespace-nowrap">
                                    {{ $event->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- My apps --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">My Apps</h2>
                <a href="{{ route('user.apps.create') }}"
                   class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">
                    + New app
                </a>
            </div>
            <div class="card-body space-y-3">
                @if($apps->isEmpty())
                    <div class="text-center py-6">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No apps yet</p>
                        <a href="{{ route('user.apps.create') }}" class="btn-primary mt-3 text-xs px-3 py-2">
                            Create your first app
                        </a>
                    </div>
                @else
                    @foreach($apps as $app)
                        <a href="{{ route('user.apps.show', $app) }}"
                           class="flex items-center gap-3 p-3 rounded-lg
                          hover:bg-neutral-50 dark:hover:bg-neutral-800
                          border border-neutral-100 dark:border-neutral-800
                          transition-colors group">
                            <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center
                                justify-center flex-shrink-0">
                        <span class="text-white text-xs font-bold">
                            {{ strtoupper(substr($app->name, 0, 1)) }}
                        </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                    {{ $app->name }}
                                </p>
                                <p class="text-xs text-neutral-400 dark:text-neutral-500">
                                    {{ number_format($app->event_logs_count) }} events
                                </p>
                            </div>
                            @if($app->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-neutral">Inactive</span>
                            @endif
                        </a>
                    @endforeach

                    @if($apps->count() === 5)
                        <a href="{{ route('user.apps.index') }}"
                           class="block text-center text-xs text-brand-600 dark:text-brand-400
                              hover:underline pt-1">
                            View all apps
                        </a>
                    @endif
                @endif
            </div>
        </div>

    </div>

@endsection