{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('content')

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

        <div class="stat-card">
            <div class="stat-icon bg-brand-50 dark:bg-brand-900/20">
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Total Users</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['total_users']) }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">{{ number_format($stats['active_users']) }} active</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple-50 dark:bg-purple-900/20">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Total Apps</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['total_apps']) }}</p>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">{{ number_format($stats['active_apps']) }} active</p>
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
            <div class="stat-icon bg-red-50 dark:bg-red-900/20">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Failed Events</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($stats['failed_events']) }}</p>
                <p class="text-xs text-red-400 mt-0.5">
                    @if($stats['failed_events'] > 0)
                        <a href="{{ route('admin.events.index') }}?status=failed" class="hover:underline">
                            Needs attention
                        </a>
                    @else
                        All clear
                    @endif
                </p>
            </div>
        </div>

    </div>

    {{-- Two column --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Recent events --}}
        <div class="xl:col-span-2 card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Events</h2>
                <a href="{{ route('admin.events.index') }}"
                   class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">
                    View all
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-32">Event</th>
                        <th class="w-40">Channel</th>
                        <th class="w-32">User</th>
                        <th class="w-28">Status</th>
                        <th class="text-right">Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentEvents as $event)
                        <tr>
                            <td>
                                <span class="font-mono text-xs bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded text-neutral-600 dark:text-neutral-300">{{ $event->event_name }}</span>
                            </td>
                            <td>
                            <span class="font-mono text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $event->channel }}
                            </span>
                            </td>
                            <td class="text-xs font-medium">
                                {{ $event->app->user->name ?? '—' }}
                            </td>
                            <td>
                                @if($event->status === 'delivered')
                                    <span class="badge-green">Delivered</span>
                                @elseif($event->status === 'failed')
                                    <span class="badge-red">Failed</span>
                                @else
                                    <span class="badge-yellow">Pending</span>
                                @endif
                            </td>
                            <td class="text-xs text-neutral-500 dark:text-neutral-400 whitespace-nowrap text-right">
                                {{ $event->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-neutral-400 text-xs">
                                No events yet
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent users --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}"
                   class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">
                    View all
                </a>
            </div>
            <div class="card-body space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-neutral-200 dark:bg-neutral-700
                            flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-300">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 dark:text-white truncate">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-neutral-400 truncate">
                                {{ $user->apps_count }} apps · {{ $user->created_at->diffForHumans() }}
                            </p>
                        </div>
                        @if($user->is_active)
                            <span class="badge-green">Active</span>
                        @else
                            <span class="badge-red">Banned</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-neutral-400 text-center py-4">No users yet</p>
                @endforelse
            </div>
        </div>

    </div>

@endsection