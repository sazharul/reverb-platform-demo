<div wire:poll.8s>

    {{-- Live indicator --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Event Logs</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">View all events fired across your apps.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            Live — auto-refreshes every 8 s
        </span>
    </div>

    {{-- Filters --}}
    <div class="card p-4 mb-5">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="label">Search</label>
                <input type="text" wire:model.live.debounce.400ms="search"
                       placeholder="Event name, channel, UUID…" class="input">
            </div>
            <div class="w-44">
                <label class="label">App</label>
                <select wire:model.live="appId" class="input">
                    <option value="">All Apps</option>
                    @foreach($apps as $app)
                        <option value="{{ $app->id }}">{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="label">Status</label>
                <select wire:model.live="status" class="input">
                    <option value="">All</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="w-40">
                <label class="label">From</label>
                <input type="date" wire:model.live="dateFrom" class="input">
            </div>
            <div class="w-40">
                <label class="label">To</label>
                <input type="date" wire:model.live="dateTo" class="input">
            </div>
            <button wire:click="resetFilters" class="btn-secondary">Reset</button>
        </div>
    </div>

    <div class="card overflow-hidden">
        @if($events->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-8 h-8 text-neutral-300 dark:text-neutral-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">No events found.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Event</th>
                        <th>Channel</th>
                        <th>App</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th class="text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($events as $event)
                        <tr wire:key="{{ $event->id }}">
                            <td>
                                <span class="font-mono text-xs bg-neutral-100 dark:bg-neutral-800 px-2 py-1 rounded text-neutral-600 dark:text-neutral-300">{{ $event->event_name }}</span>
                            </td>
                            <td><span class="font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $event->channel }}</span></td>
                            <td class="text-xs">{{ $event->app->name ?? '—' }}</td>
                            <td>
                                @if($event->status === 'delivered')
                                    <span class="badge-green">Delivered</span>
                                @elseif($event->status === 'failed')
                                    <span class="badge-red">Failed</span>
                                @else
                                    <span class="badge-yellow">Pending</span>
                                @endif
                            </td>
                            <td class="text-xs text-neutral-400 whitespace-nowrap">{{ $event->created_at->diffForHumans() }}</td>
                            <td class="text-right">
                                <a href="{{ route('user.events.show', $event) }}" class="text-sm text-brand-600 hover:underline">Details</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
                {{ $events->links() }}
            </div>
        @endif
    </div>

</div>

