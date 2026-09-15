@extends('layouts.dashboard')

@section('title', 'Users')
@section('breadcrumb', 'Admin / Users')

@section('content')
<div class="w-full">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">User Management</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage users, staff accounts, roles and access.</p>
        </div>
        @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                + Create User
            </a>
        @endcan
    </div>

    {{-- Tabs: Users / Staff --}}
    <div class="flex gap-1 mb-4 border-b border-neutral-200 dark:border-neutral-700">
        <a href="{{ route('admin.users.index', array_merge(request()->except('tab', 'page'), ['tab' => 'users'])) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                  {{ $tab !== 'staff' ? 'border-brand-600 text-brand-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300' }}">
            Regular Users
        </a>
        @if(auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.users.index', array_merge(request()->except('tab', 'page'), ['tab' => 'staff'])) }}"
           class="px-4 py-2 text-sm font-medium border-b-2 transition-colors
                  {{ $tab === 'staff' ? 'border-brand-600 text-brand-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300' }}">
            Staff & Admins
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="card p-4 mb-5">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-1 min-w-[200px]">
                <label class="label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…" class="input">
            </div>
            <div class="w-40">
                <label class="label">Status</label>
                <select name="status" class="input">
                    <option value="">All</option>
                    <option value="active"  @selected(request('status') === 'active')>Active</option>
                    <option value="banned"  @selected(request('status') === 'banned')>Banned</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('admin.users.index', ['tab' => $tab]) }}" class="btn-secondary">Reset</a>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-sm text-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm text-red-800 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>User</th>
                    @if($tab === 'staff')
                        <th>Role</th>
                    @else
                        <th>Plan</th>
                    @endif
                    <th>Apps</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-neutral-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>

                        @if($tab === 'staff')
                        <td>
                            @forelse($user->roles as $role)
                                <span class="badge-blue capitalize">{{ $role->name }}</span>
                            @empty
                                <span class="badge-neutral">No role</span>
                            @endforelse
                            @if($user->isSuperAdmin())
                                <span class="badge-red">Superadmin</span>
                            @endif
                        </td>
                        @else
                        <td>
                            <span class="badge-blue">{{ $user->activeSubscription?->plan?->name ?? 'Free' }}</span>
                        </td>
                        @endif

                        <td class="text-sm">{{ $user->apps_count }}</td>

                        <td>
                            @if($user->is_active)
                                <span class="badge-green">Active</span>
                            @else
                                <span class="badge-red">Banned</span>
                            @endif
                        </td>

                        <td class="text-xs text-neutral-400 whitespace-nowrap">
                            {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-brand-600 hover:underline">View</a>
                                @can('users.edit')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Edit</a>
                                @endcan
                                @can('users.edit')
                                    @if(!$user->isSuperAdmin())
                                    <form action="{{ route('admin.users.toggle-ban', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm {{ $user->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                            {{ $user->is_active ? 'Ban' : 'Activate' }}
                                        </button>
                                    </form>
                                    @endif
                                @endcan
                                @can('users.delete')
                                    @if(!$user->isSuperAdmin())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-neutral-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="text-sm">No {{ $tab === 'staff' ? 'staff members' : 'users' }} found.</p>
                                @can('users.create')
                                    <a href="{{ route('admin.users.create') }}" class="text-sm text-brand-600 hover:underline">Create one now →</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

