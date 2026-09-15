@extends('layouts.dashboard')

@section('title', 'All Apps')
@section('breadcrumb', 'Admin / Apps')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">All Apps</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage apps across all users.</p>
        </div>
    </div>

    <div class="card p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, App ID, or App Key…" class="input">
            </div>
            <div class="w-40">
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.apps.index') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>App</th>
                    <th>Owner</th>
                    <th>App ID</th>
                    <th>Channels</th>
                    <th>Events</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($apps as $app)
                    <tr>
                        <td class="text-sm font-medium text-neutral-900 dark:text-white">{{ $app->name }}</td>
                        <td class="text-xs text-neutral-500">{{ $app->user->name ?? '—' }}</td>
                        <td class="text-xs font-mono text-neutral-500">{{ $app->app_id }}</td>
                        <td class="text-sm">{{ $app->channels_count }}</td>
                        <td class="text-sm">{{ number_format($app->event_logs_count) }}</td>
                        <td>@if($app->is_active)<span class="badge-green">Active</span>@else<span class="badge-neutral">Inactive</span>@endif</td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.apps.show', $app) }}" class="text-sm text-brand-600 hover:underline">View</a>
                                <a href="{{ route('admin.apps.edit', $app) }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-neutral-400 text-xs">No apps found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">{{ $apps->links() }}</div>
    </div>
@endsection

