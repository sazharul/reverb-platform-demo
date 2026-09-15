@extends('layouts.dashboard')

@section('title', 'Create Channel')
@section('breadcrumb', 'Dashboard / Channels / Create')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Create a new channel</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Channels are scoped to an app. Private channels auto-prefix with <code>private-</code>.</p>
        </div>

        <div class="card p-5">
            <form action="{{ route('user.channels.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="app_id" class="label">App</label>
                    <select id="app_id" name="app_id" class="input @error('app_id') input-error @enderror" required>
                        <option value="">Select an app…</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}" @selected(old('app_id', request('app_id')) == $app->id)>{{ $app->name }}</option>
                        @endforeach
                    </select>
                    @error('app_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="name" class="label">Channel Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name') }}" placeholder="e.g. orders" required>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">For private channels, <code>private-</code> prefix is added automatically.</p>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="label">Channel Type</label>
                    <select id="type" name="type" class="input @error('type') input-error @enderror" required>
                        <option value="public" @selected(old('type', 'public') === 'public')>Public</option>
                        <option value="private" @selected(old('type') === 'private')>Private</option>
                        <option value="presence" @selected(old('type') === 'presence')>Presence</option>
                    </select>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        <strong>Public:</strong> No auth needed. <strong>Private:</strong> Server-side auth required. <strong>Presence:</strong> Auth + user info.
                    </p>
                    @error('type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Create Channel</button>
                    <a href="{{ route('user.channels.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

