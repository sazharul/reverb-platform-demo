@extends('layouts.dashboard')

@section('title', $user->name)
@section('breadcrumb', 'Admin / Users / ' . $user->name)

@section('content')
<div class="w-full space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-brand-600 flex items-center justify-center flex-shrink-0">
                <span class="text-white font-bold text-lg">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $user->name }}</h2>
                    <span class="badge-{{ $user->isSuperAdmin() ? 'red' : ($user->isAdminType() ? 'blue' : 'neutral') }} capitalize">
                        {{ $user->user_type }}
                    </span>
                </div>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $user->email }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('users.edit')
                <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">Edit User</a>
                @if(!$user->isSuperAdmin())
                <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="{{ $user->is_active ? 'btn-danger' : 'btn-primary' }}">
                        {{ $user->is_active ? 'Ban User' : 'Activate User' }}
                    </button>
                </form>
                @endif
            @endcan
            @can('users.delete')
                @if(!$user->isSuperAdmin())
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Permanently delete this user?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
                @endif
            @endcan
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Back</a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card">
            <p class="text-xs text-neutral-500 font-medium">Status</p>
            @if($user->is_active)<span class="badge-green">Active</span>@else<span class="badge-red">Banned</span>@endif
        </div>
        <div class="stat-card">
            <p class="text-xs text-neutral-500 font-medium">Plan</p>
            <span class="badge-blue">{{ $user->activeSubscription?->plan?->name ?? 'Free' }}</span>
        </div>
        <div class="stat-card">
            <p class="text-xs text-neutral-500 font-medium">Apps</p>
            <p class="text-xl font-bold text-neutral-900 dark:text-white">{{ $user->apps->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs text-neutral-500 font-medium">Last Login</p>
            <p class="text-sm text-neutral-700 dark:text-neutral-200">{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left / main column --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Roles & Permissions card --}}
            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('roles.assign'))
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Roles & Permissions</h3>
                    @can('users.edit')
                        <a href="{{ route('admin.users.edit', $user) }}#roles" class="text-xs text-brand-600 hover:underline">Edit roles →</a>
                    @endcan
                </div>

                {{-- Assigned Roles --}}
                <div class="mb-4">
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Assigned Roles</p>
                    @if($user->roles->count())
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-sm font-medium capitalize">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    {{ $role->name }}
                                    <span class="text-xs text-brand-500 dark:text-brand-400">({{ $role->permissions->count() }} perms)</span>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-neutral-400 italic">No roles assigned.</p>
                    @endif
                </div>

                {{-- Effective Permissions (from roles) --}}
                @php
                    $effectivePerms = $user->getPermissionsViaRoles()->pluck('name')->sort()->groupBy(fn($p) => explode('.', $p)[0]);
                    $directPerms   = $user->permissions->pluck('name')->sort()->values();
                @endphp

                @if($effectivePerms->count())
                <div class="mb-4">
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Effective Permissions (via roles)</p>
                    <div class="space-y-2">
                        @foreach($effectivePerms as $group => $perms)
                            <div>
                                <span class="text-xs font-medium text-neutral-400 uppercase">{{ $group }}</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($perms as $perm)
                                        <span class="text-xs px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300">{{ Str::after($perm, '.') }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Direct Permissions --}}
                @if($directPerms->count())
                <div>
                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-2">Direct Permissions (overrides)</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($directPerms as $perm)
                            <span class="text-xs px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 font-medium">{{ $perm }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- User's Apps --}}
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Apps ({{ $user->apps->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Name</th><th>App ID</th><th>Events</th><th>Status</th><th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($user->apps as $app)
                            <tr>
                                <td class="text-sm font-medium">{{ $app->name }}</td>
                                <td class="text-xs font-mono text-neutral-500">{{ $app->app_id }}</td>
                                <td class="text-sm">{{ number_format($app->event_logs_count) }}</td>
                                <td>@if($app->is_active)<span class="badge-green">Active</span>@else<span class="badge-neutral">Inactive</span>@endif</td>
                                <td class="text-right"><a href="{{ route('admin.apps.show', $app) }}" class="text-sm text-brand-600 hover:underline">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-6 text-neutral-400 text-xs">No apps.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right sidebar --}}
        <div class="space-y-5">

            {{-- Account details --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4 pb-3 border-b border-neutral-200 dark:border-neutral-700">Account Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-neutral-500">User ID</dt>
                        <dd class="font-medium text-neutral-900 dark:text-white">{{ $user->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-neutral-500">Type</dt>
                        <dd class="capitalize font-medium text-neutral-900 dark:text-white">{{ $user->user_type }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-neutral-500">Registered</dt>
                        <dd class="text-neutral-700 dark:text-neutral-300">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-neutral-500">Last Login IP</dt>
                        <dd class="font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $user->last_login_ip ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Assign Subscription Plan --}}
            @can('subscriptions.assign')
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4 pb-3 border-b border-neutral-200 dark:border-neutral-700">Assign Subscription Plan</h3>
                <form action="{{ route('admin.users.assign-plan', $user) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label">Plan</label>
                        <select name="subscription_plan_id" class="input" required>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected($user->activeSubscription?->subscription_plan_id == $plan->id)>
                                    {{ $plan->name }} — ${{ $plan->price }}/{{ $plan->billing_cycle }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Expires At (optional)</label>
                        <input type="date" name="expires_at" class="input">
                    </div>
                    <div>
                        <label class="label">Notes</label>
                        <input type="text" name="notes" class="input" placeholder="e.g. Manual upgrade by admin">
                    </div>
                    <button type="submit" class="btn-primary w-full justify-center">Assign Plan</button>
                </form>
            </div>
            @endcan

        </div>
    </div>
</div>
@endsection

