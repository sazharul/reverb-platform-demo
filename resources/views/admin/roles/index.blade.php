@extends('layouts.dashboard')

@section('title', 'Roles & Permissions')
@section('breadcrumb', 'Admin / Roles & Permissions')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Roles & Permissions</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage admin roles and their permissions.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn-primary">+ Create Role</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>Role</th>
                    <th>Permissions</th>
                    <th>Users</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td class="text-sm font-medium text-neutral-900 dark:text-white">{{ $role->name }}</td>
                        <td><span class="badge-blue">{{ $role->permissions_count }} permissions</span></td>
                        <td><span class="badge-neutral">{{ $role->users_count }} users</span></td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-sm text-brand-600 hover:underline">Edit</a>
                                @if(!in_array($role->name, ['admin', 'support', 'billing']))
                                    <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Delete this role?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

