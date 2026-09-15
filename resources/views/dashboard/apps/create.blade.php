@extends('layouts.dashboard')

@section('title', 'Create App')
@section('breadcrumb', 'Dashboard / My Apps / Create')

@section('content')
    <div class="w-full">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Create a new app</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">A unique app ID, app key, and app secret are generated automatically.</p>
        </div>

        <div class="card p-5">
            <form action="{{ route('user.apps.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="label">App Name</label>
                    <input id="name" name="name" type="text" class="input @error('name') input-error @enderror" value="{{ old('name') }}" placeholder="e.g. Enorsia Storefront" required>
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="label">Description (optional)</label>
                    <textarea id="description" name="description" rows="3" class="input @error('description') input-error @enderror" placeholder="What is this app used for?">{{ old('description') }}</textarea>
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="allowed_origins" class="label">Allowed Origins (optional)</label>
                    <textarea id="allowed_origins" name="allowed_origins" rows="3" class="input @error('allowed_origins') input-error @enderror" placeholder="https://example.com&#10;https://app.example.com">{{ old('allowed_origins') }}</textarea>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Enter one origin per line, or comma-separated.</p>
                    @error('allowed_origins') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="max_connections" class="label">Max Connections</label>
                    <input id="max_connections" name="max_connections" type="number" min="1" class="input @error('max_connections') input-error @enderror" value="{{ old('max_connections', 200) }}" required>
                    @error('max_connections') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="webhook_url" class="label">Webhook URL (optional)</label>
                    <input id="webhook_url" name="webhook_url" type="url" class="input @error('webhook_url') input-error @enderror" value="{{ old('webhook_url') }}" placeholder="https://your-server.com/webhook">
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Receive POST notifications when events are delivered. Requires a plan with webhook support.</p>
                    @error('webhook_url') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                {{-- ── Initial Channels ─────────────────────────────── --}}
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="label mb-0">Initial Channels (optional)</label>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">Create channels now, or add them later.</p>
                        </div>
                        <button type="button" id="add-channel-btn" class="btn-secondary text-xs">+ Add Channel</button>
                    </div>

                    <div id="channels-container" class="space-y-3">
                        @if(old('channels'))
                            @foreach(old('channels') as $i => $ch)
                                <div class="channel-row flex items-start gap-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-3">
                                    <div class="flex-1">
                                        <input type="text" name="channels[{{ $i }}][name]" class="input text-sm" value="{{ $ch['name'] ?? '' }}" placeholder="Channel name (e.g. orders)">
                                        @error("channels.{$i}.name") <p class="field-error">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="w-36">
                                        <select name="channels[{{ $i }}][type]" class="input text-sm">
                                            <option value="public" @selected(($ch['type'] ?? '') === 'public')>Public</option>
                                            <option value="private" @selected(($ch['type'] ?? '') === 'private')>Private</option>
                                            <option value="presence" @selected(($ch['type'] ?? '') === 'presence')>Presence</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="this.closest('.channel-row').remove()" class="mt-1 text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="btn-primary">Create App</button>
                    <a href="{{ route('user.apps.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        let channelIndex = {{ old('channels') ? count(old('channels')) : 0 }};
        document.getElementById('add-channel-btn').addEventListener('click', function () {
            const container = document.getElementById('channels-container');
            const html = `
                <div class="channel-row flex items-start gap-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-3">
                    <div class="flex-1">
                        <input type="text" name="channels[${channelIndex}][name]" class="input text-sm" placeholder="Channel name (e.g. orders)">
                    </div>
                    <div class="w-36">
                        <select name="channels[${channelIndex}][type]" class="input text-sm">
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                            <option value="presence">Presence</option>
                        </select>
                    </div>
                    <button type="button" onclick="this.closest('.channel-row').remove()" class="mt-1 text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            channelIndex++;
        });
    </script>
    @endpush
@endsection

