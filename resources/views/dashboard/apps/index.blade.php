@extends('layouts.dashboard')

@section('title', 'My Apps')
@section('breadcrumb', 'Dashboard / My Apps')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">My Apps</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage your realtime app credentials.</p>
        </div>
        <a href="{{ route('user.apps.create') }}" class="btn-primary">+ Create App</a>
    </div>

    <div class="card overflow-hidden">
        @if($apps->isEmpty())
            <div class="p-8 text-center">
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No apps created yet.</p>
                <a href="{{ route('user.apps.create') }}" class="btn-primary mt-4">Create your first app</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                    <thead class="bg-neutral-50 dark:bg-neutral-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">App ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">App Key</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">Channels</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">Events</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-neutral-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-neutral-500">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @foreach($apps as $app)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $app->name }}</p>
                                @if($app->description)
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ \Illuminate\Support\Str::limit($app->description, 70) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-mono text-neutral-700 dark:text-neutral-300">{{ $app->app_id }}</td>
                            <td class="px-4 py-3 text-xs font-mono text-neutral-700 dark:text-neutral-300">{{ $app->app_key }}</td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ number_format($app->channels_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">{{ number_format($app->event_logs_count) }}</td>
                            <td class="px-4 py-3">
                                @if($app->is_active)
                                    <span class="badge-green">Active</span>
                                @else
                                    <span class="badge-neutral">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('user.apps.show', $app) }}" class="text-sm text-brand-600 hover:underline">View</a>
                                    <a href="{{ route('user.apps.edit', $app) }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Edit</a>
                                    <form action="{{ route('user.apps.destroy', $app) }}" method="POST" onsubmit="return confirm('Delete this app and all related channels/events?')">
                                        @csrf
                                        @method('DELETE')
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
                {{ $apps->links() }}
            </div>
        @endif
    </div>
@endsection

