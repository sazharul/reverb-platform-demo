@extends('layouts.dashboard')

@section('title', 'Edit App')
@section('breadcrumb', 'Dashboard / My Apps / Edit')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Edit {{ $reverbApp->name }}</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">You can update settings without changing app key or app secret.</p>
        </div>

        <div class="card p-5">
            <form action="{{ route('user.apps.update', $reverbApp) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="label">App Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name', $reverbApp->name) }}" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="3" class="input @error('description') input-error @enderror">{{ old('description', $reverbApp->description) }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="allowed_origins" class="label">Allowed Origins</label>
                    <textarea id="allowed_origins" name="allowed_origins" rows="3" class="input @error('allowed_origins') input-error @enderror">{{ old('allowed_origins', implode(PHP_EOL, $reverbApp->allowed_origins ?? [])) }}</textarea>
                    @error('allowed_origins') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="max_connections" class="label">Max Connections</label>
                    <input id="max_connections" name="max_connections" type="number" min="1" class="input @error('max_connections') input-error @enderror" value="{{ old('max_connections', $reverbApp->max_connections) }}" required>
                    @error('max_connections') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="webhook_url" class="label">Webhook URL (optional)</label>
                    <input id="webhook_url" name="webhook_url" type="url" class="input @error('webhook_url') input-error @enderror" value="{{ old('webhook_url', $reverbApp->webhook_url) }}" placeholder="https://your-server.com/webhook">
                    @error('webhook_url') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $reverbApp->is_active))>
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">App is active</span>
                </label>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="{{ route('user.apps.show', $reverbApp) }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

