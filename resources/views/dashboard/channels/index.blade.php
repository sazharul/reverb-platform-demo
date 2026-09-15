@extends('layouts.dashboard')

@section('title', 'Channels')
@section('breadcrumb', 'Dashboard / Channels')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Channels</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage channels across your apps.</p>
        </div>
        <a href="{{ route('user.channels.create') }}" class="btn-primary">+ Create Channel</a>
    </div>

    {{-- Filters --}}
    <div class="card p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Channel name…" class="input">
            </div>
            <div class="w-48">
                <label class="label">App</label>
                <select name="app" class="input">
                    <option value="">All Apps</option>
                    @foreach($apps as $app)
                        <option value="{{ $app->id }}" @selected(request('app') == $app->id)>{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-40">
                <label class="label">Type</label>
                <select name="type" class="input">
                    <option value="">All Types</option>
                    <option value="public" @selected(request('type') === 'public')>Public</option>
                    <option value="private" @selected(request('type') === 'private')>Private</option>
                    <option value="presence" @selected(request('type') === 'presence')>Presence</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('user.channels.index') }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    <div class="card overflow-hidden">
        @if($channels->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No channels found.</p>
                <a href="{{ route('user.channels.create') }}" class="btn-primary mt-4">Create your first channel</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Channel Name</th>
                        <th>App</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($channels as $channel)
                        <tr>
                            <td>
                                <span class="font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $channel->name }}</span>
                            </td>
                            <td class="text-xs">{{ $channel->app->name ?? '—' }}</td>
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
                            <td class="text-xs text-neutral-400 whitespace-nowrap">{{ $channel->created_at->diffForHumans() }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('user.channels.edit', $channel) }}" class="text-sm text-brand-600 hover:underline">Edit</a>
                                    <form action="{{ route('user.channels.destroy', $channel) }}" method="POST" onsubmit="return confirm('Delete this channel?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
                {{ $channels->links() }}
            </div>
        @endif
    </div>
@endsection

