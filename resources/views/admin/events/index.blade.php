@extends('layouts.dashboard')

@section('title', 'Event Logs')
@section('breadcrumb', 'Admin / Event Logs')

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Global Event Logs</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">All events across all apps and users.</p>
    </div>

    <div class="card p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Event name, channel, UUID…" class="input">
            </div>
            <div class="w-44">
                <label class="label">App</label>
                <select name="app" class="input">
                    <option value="">All Apps</option>
                    @foreach($apps as $app)
                        <option value="{{ $app->id }}" @selected(request('app') == $app->id)>{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="">All</option>
                    <option value="delivered" @selected(request('status') === 'delivered')>Delivered</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                </select>
            </div>
            <div class="w-40">
                <label class="label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input">
            </div>
            <div class="w-40">
                <label class="label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input">
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.events.index') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>Event</th>
                    <th>Channel</th>
                    <th>App</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Time</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($events as $event)
                    <tr>
                        <td><span class="font-mono text-xs bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded">{{ $event->event_name }}</span></td>
                        <td><span class="font-mono text-xs text-neutral-500">{{ $event->channel }}</span></td>
                        <td class="text-xs">{{ $event->app->name ?? '—' }}</td>
                        <td class="text-xs">{{ $event->app->user->name ?? '—' }}</td>
                        <td>
                            @if($event->status === 'delivered')<span class="badge-green">Delivered</span>
                            @elseif($event->status === 'failed')<span class="badge-red">Failed</span>
                            @else<span class="badge-yellow">Pending</span>@endif
                        </td>
                        <td class="text-xs text-neutral-400 whitespace-nowrap">{{ $event->created_at->diffForHumans() }}</td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-brand-600 hover:underline">Details</a>
                                @if($event->status === 'failed')
                                    <form action="{{ route('admin.events.retry', $event) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-green-600 hover:underline">Retry</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-neutral-400 text-xs">No events found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">{{ $events->links() }}</div>
    </div>
@endsection

