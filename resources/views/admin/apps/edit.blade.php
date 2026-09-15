@extends('layouts.dashboard')

@section('title', 'Edit App')
@section('breadcrumb', 'Admin / Apps / Edit')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Edit {{ $app->name }}</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Owned by {{ $app->user->name ?? '—' }}</p>
        </div>
        <div class="card p-5">
            <form action="{{ route('admin.apps.update', $app) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')
                <div>
                    <label class="label">App Name</label>
                    <input name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name', $app->name) }}" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Max Connections</label>
                    <input name="max_connections" type="number" min="1" class="input" value="{{ old('max_connections', $app->max_connections) }}" required>
                </div>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $app->is_active))>
                    <span class="text-sm text-neutral-700 dark:text-neutral-200">App is active</span>
                </label>
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save</button>
                    <a href="{{ route('admin.apps.show', $app) }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

