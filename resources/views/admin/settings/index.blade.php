@extends('layouts.dashboard')

@section('title', 'System Settings')
@section('breadcrumb', 'Admin / System Settings')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">System Settings</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Global configuration for the platform.</p>
        </div>

        <div class="card p-5">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3 pb-2 border-b border-neutral-200 dark:border-neutral-700">General</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Site Name</label>
                            <input name="site_name" type="text" class="input" value="{{ old('site_name', $settings['site_name']) }}" required>
                        </div>
                        <div>
                            <label class="label">Registration Enabled</label>
                            <select name="registration_enabled" class="input">
                                <option value="1" @selected($settings['registration_enabled'] === '1')>Yes</option>
                                <option value="0" @selected($settings['registration_enabled'] === '0')>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Maintenance Mode</label>
                            <select name="maintenance_mode" class="input">
                                <option value="0" @selected($settings['maintenance_mode'] === '0')>Off</option>
                                <option value="1" @selected($settings['maintenance_mode'] === '1')>On</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-3 pb-2 border-b border-neutral-200 dark:border-neutral-700">Defaults</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="label">Default Max Connections (per app)</label>
                            <input name="default_max_connections" type="number" min="1" class="input" value="{{ old('default_max_connections', $settings['default_max_connections']) }}" required>
                        </div>
                        <div>
                            <label class="label">Default Daily Message Limit</label>
                            <input name="default_daily_message_limit" type="number" min="1" class="input" value="{{ old('default_daily_message_limit', $settings['default_daily_message_limit']) }}" required>
                        </div>
                        <div>
                            <label class="label">Event Log Retention (days)</label>
                            <input name="event_log_retention_days" type="number" min="1" max="365" class="input" value="{{ old('event_log_retention_days', $settings['event_log_retention_days']) }}" required>
                            <p class="text-xs text-neutral-500 mt-1">Logs older than this will be purged automatically.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
@endsection

