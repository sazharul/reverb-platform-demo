@extends('layouts.dashboard')

@section('title', 'Edit Channel')
@section('breadcrumb', 'Dashboard / Channels / Edit')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Edit Channel</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Update channel settings.</p>
        </div>

        <div class="card p-5">
            <form action="{{ route('user.channels.update', $channel) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="name" class="label">Channel Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name', $channel->name) }}" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="label">Channel Type</label>
                    <select id="type" name="type" class="input @error('type') input-error @enderror" required>
                        <option value="public" @selected(old('type', $channel->type) === 'public')>Public</option>
                        <option value="private" @selected(old('type', $channel->type) === 'private')>Private</option>
                        <option value="presence" @selected(old('type', $channel->type) === 'presence')>Presence</option>
                    </select>
                    @error('type') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $channel->is_active))>
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">Channel is active</span>
                </label>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="{{ route('user.channels.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

