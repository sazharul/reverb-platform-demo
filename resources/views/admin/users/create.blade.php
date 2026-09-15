@extends('layouts.dashboard')

@section('title', 'Create User')
@section('breadcrumb', 'Admin / Users / Create')

@section('content')
<div class="w-full max-w-5xl">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Create New User</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">Add a new user or staff member to the platform.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn-secondary">← Back to Users</a>
    </div>

    @if($errors->any())
        <div class="mb-5 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
            <p class="text-sm font-medium text-red-800 dark:text-red-300 mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5" id="createUserForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Left column: Basic Info --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Basic Information --}}
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4 pb-3 border-b border-neutral-200 dark:border-neutral-700">
                        Basic Information
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="label">Full Name <span class="text-red-500">*</span></label>
                            <input id="name" name="name" type="text"
                                   class="input @error('name') input-error @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="John Doe"
                                   required autofocus>
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="label">Email Address <span class="text-red-500">*</span></label>
                            <input id="email" name="email" type="email"
                                   class="input @error('email') input-error @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="john@example.com"
                                   required>
                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="label">Password <span class="text-red-500">*</span></label>
                                <input id="password" name="password" type="password"
                                       class="input @error('password') input-error @enderror"
                                       placeholder="Min 8 chars, upper+lower+number"
                                       required>
                                @error('password') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="label">Confirm Password <span class="text-red-500">*</span></label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                       class="input"
                                       placeholder="Repeat password"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Roles (admin-type only) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->can('roles.assign'))
                <div class="card p-5" id="rolesSection" style="{{ old('user_type', 'user') === 'user' ? 'display:none' : '' }}">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-1 pb-3 border-b border-neutral-200 dark:border-neutral-700">
                        Assign Role
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-4">Roles bundle permissions together. A staff member should have at least one role.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($roles as $role)
                            <label class="flex items-start gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                       class="mt-0.5 accent-brand-600"
                                       @checked(in_array($role->name, old('roles', [])))>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white capitalize">{{ $role->name }}</p>
                                    <p class="text-xs text-neutral-400">{{ $role->permissions_count ?? $role->permissions->count() }} permissions</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="field-error mt-2">{{ $message }}</p> @enderror
                </div>
                @endif

                {{-- Direct Permissions (superadmin only, advanced) --}}
                @if(auth()->user()->isSuperAdmin())
                <div class="card" id="permissionsSection" style="{{ old('user_type', 'user') === 'user' ? 'display:none' : '' }}">
                    <button type="button"
                            class="w-full flex items-center justify-between p-5 text-left"
                            onclick="document.getElementById('permissionsBody').classList.toggle('hidden')">
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Direct Permissions
                                <span class="ml-2 text-xs font-normal text-neutral-400">(Advanced — overrides role permissions)</span>
                            </h3>
                        </div>
                        <svg class="w-4 h-4 text-neutral-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="permissionsBody" class="hidden px-5 pb-5 border-t border-neutral-200 dark:border-neutral-700 pt-4 space-y-4">
                        @foreach($permissions as $group => $perms)
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ $group }}</p>
                                    <button type="button"
                                            class="text-xs text-brand-600 hover:underline"
                                            onclick="toggleGroup('perm_{{ $group }}')">toggle all</button>
                                </div>
                                <div class="flex flex-wrap gap-2" id="perm_{{ $group }}">
                                    @foreach($perms as $perm)
                                        <label class="inline-flex items-center gap-1.5 text-sm text-neutral-700 dark:text-neutral-200 cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                                   class="accent-brand-600"
                                                   @checked(in_array($perm->name, old('permissions', [])))>
                                            <span>{{ Str::after($perm->name, '.') }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

            {{-- Right column: Account Settings --}}
            <div class="space-y-5">

                {{-- Account Type --}}
                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4 pb-3 border-b border-neutral-200 dark:border-neutral-700">
                        Account Settings
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Account Type <span class="text-red-500">*</span></label>
                            <div class="space-y-2 mt-1">
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                                    <input type="radio" name="user_type" value="user"
                                           class="accent-brand-600"
                                           @checked(old('user_type', 'user') === 'user')
                                           onchange="handleUserTypeChange(this.value)">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Regular User</p>
                                        <p class="text-xs text-neutral-400">Can use the platform, create apps</p>
                                    </div>
                                </label>
                                @if(auth()->user()->isSuperAdmin())
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-900/20 transition-colors">
                                    <input type="radio" name="user_type" value="admin"
                                           class="accent-brand-600"
                                           @checked(old('user_type') === 'admin')
                                           onchange="handleUserTypeChange(this.value)">
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900 dark:text-white">Staff / Admin</p>
                                        <p class="text-xs text-neutral-400">Has access to admin panel based on assigned role</p>
                                    </div>
                                </label>
                                @endif
                            </div>
                            @error('user_type') <p class="field-error mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-2 border-t border-neutral-100 dark:border-neutral-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1"
                                       class="accent-brand-600"
                                       @checked(old('is_active', '1') == '1')>
                                <div>
                                    <p class="text-sm font-medium text-neutral-900 dark:text-white">Active Account</p>
                                    <p class="text-xs text-neutral-400">User can log in immediately</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Summary hint --}}
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-1">💡 How it works</p>
                    <ul class="text-xs text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                        <li><strong>Regular users</strong> access the dashboard to manage their own apps.</li>
                        <li><strong>Staff/Admin</strong> accounts get admin panel access controlled by their assigned role.</li>
                        <li>Roles bundle permissions. Direct permissions are individual overrides.</li>
                    </ul>
                </div>

                {{-- Actions --}}
                <div class="card p-4 space-y-2">
                    <button type="submit" class="btn-primary w-full justify-center">
                        Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary w-full justify-center text-center block">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
function handleUserTypeChange(type) {
    const rolesSection = document.getElementById('rolesSection');
    const permissionsSection = document.getElementById('permissionsSection');
    if (rolesSection) rolesSection.style.display = (type === 'admin') ? '' : 'none';
    if (permissionsSection) permissionsSection.style.display = (type === 'admin') ? '' : 'none';
}

function toggleGroup(id) {
    const container = document.getElementById(id);
    if (!container) return;
    const checkboxes = container.querySelectorAll('input[type=checkbox]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
@endsection

