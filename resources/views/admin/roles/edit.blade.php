@extends('layouts.dashboard')

@section('title', 'Edit Role')
@section('breadcrumb', 'Admin / Roles / Edit')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Edit Role: {{ $role->name }}</h2>
        </div>
        <div class="card p-5">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label for="name" class="label">Role Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name', $role->name) }}" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label">Permissions</label>
                    <div class="space-y-4 mt-2">
                        @foreach($permissions as $group => $perms)
                            <div>
                                <p class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-2">{{ $group }}</p>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($perms as $perm)
                                        <label class="inline-flex items-center gap-1.5">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name, old('permissions', $rolePermissions)))>
                                            <span class="text-sm text-neutral-700 dark:text-neutral-200">{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Update Role</button>
                    <a href="{{ route('admin.roles.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

