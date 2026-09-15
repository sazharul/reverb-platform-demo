@extends('layouts.dashboard')

@section('title', 'Create Plan')
@section('breadcrumb', 'Admin / Plans / Create')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Create Subscription Plan</h2>
        </div>
        <div class="card p-5">
            <form action="{{ route('admin.plans.store') }}" method="POST" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Plan Name</label>
                        <input name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name') }}" required>
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Price ($)</label>
                        <input name="price" type="number" step="0.01" min="0" class="input" value="{{ old('price', '0') }}" required>
                    </div>
                    <div>
                        <label class="label">Billing Cycle</label>
                        <select name="billing_cycle" class="input">
                            <option value="monthly" @selected(old('billing_cycle') === 'monthly')>Monthly</option>
                            <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly</option>
                            <option value="lifetime" @selected(old('billing_cycle') === 'lifetime')>Lifetime</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Sort Order</label>
                        <input name="sort_order" type="number" min="0" class="input" value="{{ old('sort_order', '0') }}">
                    </div>
                </div>

                <div>
                    <label class="label">Description</label>
                    <textarea name="description" rows="3" class="input" placeholder="Brief description of this plan">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Max Apps</label>
                        <input name="max_apps" type="number" min="1" class="input" value="{{ old('max_apps', '5') }}" required>
                    </div>
                    <div>
                        <label class="label">Max Connections / App</label>
                        <input name="max_connections_per_app" type="number" min="1" class="input" value="{{ old('max_connections_per_app', '100') }}" required>
                    </div>
                    <div>
                        <label class="label">Daily Message Limit</label>
                        <input name="daily_message_limit" type="number" min="1" class="input" value="{{ old('daily_message_limit', '10000') }}" required>
                    </div>
                    <div>
                        <label class="label">Max Channels / App</label>
                        <input name="max_channels_per_app" type="number" min="1" class="input" value="{{ old('max_channels_per_app', '50') }}" required>
                    </div>
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="webhook_allowed" value="1" @checked(old('webhook_allowed'))>
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">Webhook support included</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="allow_any_channel" value="1" @checked(old('allow_any_channel'))>
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">Allow any channel name (no pre-registration required)</span>
                </label>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Create Plan</button>
                    <a href="{{ route('admin.plans.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

