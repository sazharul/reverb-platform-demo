@extends('layouts.dashboard')

@section('title', 'Settings')
@section('breadcrumb', 'Dashboard / Settings')

@section('content')
    <div class="w-full space-y-6">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Account Settings</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage your profile, password, and API tokens.</p>
        </div>

        {{-- Plan summary --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Current Plan</h3>
                <a href="{{ route('user.plans.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">View all plans</a>
            </div>
            <div class="flex flex-wrap gap-6 text-sm">
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Plan</p>
                    <p class="font-semibold text-neutral-900 dark:text-white">{{ $usage['plan_name'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Apps</p>
                    <p class="font-medium text-neutral-700 dark:text-neutral-200">{{ $usage['apps_used'] }} / {{ $usage['apps_limit'] >= 999999 ? '∞' : $usage['apps_limit'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Daily Messages</p>
                    <p class="font-medium text-neutral-700 dark:text-neutral-200">{{ number_format($usage['messages_today']) }} / {{ $usage['messages_daily_limit'] >= 999999 ? '∞' : number_format($usage['messages_daily_limit']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Max Connections</p>
                    <p class="font-medium text-neutral-700 dark:text-neutral-200">{{ $usage['max_connections'] >= 999999 ? '∞' : number_format($usage['max_connections']) }} / app</p>
                </div>
            </div>
        </div>

        {{-- Profile --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Profile</h3>
            <form action="{{ route('user.settings.profile') }}" method="POST" class="space-y-4 w-full">
                @csrf
                <div>
                    <label for="name" class="label">Full Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">Email Address</label>
                    <input id="email" name="email" type="email" class="input @error('email') input-error @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">Update Profile</button>
            </form>
        </div>

        {{-- Password --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Change Password</h3>
            <form action="{{ route('user.settings.password') }}" method="POST" class="space-y-4 w-full">
                @csrf
                <div>
                    <label for="current_password" class="label">Current Password</label>
                    <input id="current_password" name="current_password" type="password" class="input @error('current_password') input-error @enderror" required>
                    @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label">New Password</label>
                    <input id="password" name="password" type="password" class="input @error('password') input-error @enderror" required>
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input" required>
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>

        {{-- API Tokens --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">API Tokens</h3>

            @if(session('new_token'))
                <div class="alert-success mb-4">
                    <p class="font-semibold text-xs mb-1">New token created — copy it now, it won't be shown again:</p>
                    <code class="code-inline break-all">{{ session('new_token') }}</code>
                </div>
            @endif

            <form action="{{ route('user.settings.tokens.store') }}" method="POST" class="flex items-end gap-3 mb-5 w-full">
                @csrf
                <div class="flex-1">
                    <label for="token_name" class="label">Token Name</label>
                    <input id="token_name" name="token_name" type="text" class="input" placeholder="e.g. production-server" required>
                </div>
                <button type="submit" class="btn-primary">Create Token</button>
            </form>

            @if($tokens->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Last Used</th>
                            <th>Created</th>
                            <th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($tokens as $token)
                            <tr>
                                <td class="text-sm font-medium">{{ $token->name }}</td>
                                <td class="text-xs text-neutral-400">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                <td class="text-xs text-neutral-400">{{ $token->created_at->diffForHumans() }}</td>
                                <td class="text-right">
                                    <form action="{{ route('user.settings.tokens.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Revoke this token?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Revoke</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-neutral-400 dark:text-neutral-500">No tokens yet.</p>
            @endif
        </div>
    </div>
@endsection

