<x-pulse::card :cols="$cols ?? 8" :rows="$rows ?? 2" :class="$class ?? ''">
    <x-pulse::card-header name="Reverb Messages">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
            </svg>
        </x-slot:icon>
        <x-slot:after>
            <x-pulse::select wire:model.live="period" label="Period" :options="$this->periodOptions()"
                             @change="period = $event.target.value" />
        </x-slot:after>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand ?? false">
        {{-- Summary bar --}}
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                Total in period
            </span>
            <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                {{ number_format($totalMessages) }}
                <span class="text-xs font-normal text-gray-400">events</span>
            </span>
        </div>

        {{-- Per-app breakdown --}}
        @if ($topApps->isEmpty())
            <x-pulse::no-results>No Reverb messages recorded yet.</x-pulse::no-results>
        @else
            <x-pulse::table>
                <colgroup>
                    <col />
                    <col class="w-28" />
                    <col class="w-24" />
                </colgroup>
                <x-pulse::thead>
                    <tr>
                        <x-pulse::th>App</x-pulse::th>
                        <x-pulse::th class="text-right">Messages</x-pulse::th>
                        <x-pulse::th class="text-right">Share</x-pulse::th>
                    </tr>
                </x-pulse::thead>
                <tbody>
                    @foreach ($topApps as $app)
                        @php
                            $pct = $totalMessages > 0 ? round(($app->count / $totalMessages) * 100, 1) : 0;
                        @endphp
                        <tr class="group" wire:key="app-{{ $app->app_id }}">
                            <x-pulse::td>
                                <div class="flex items-center gap-2">
                                    {{-- Colour dot per app --}}
                                    <span class="inline-block w-2 h-2 rounded-full bg-purple-500 opacity-75 flex-shrink-0"></span>
                                    <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[200px]"
                                          title="{{ $app->app_name }}">
                                        {{ $app->app_name }}
                                    </span>
                                    <span class="text-xs text-gray-400 font-mono truncate max-w-[120px]"
                                          title="{{ $app->app_key }}">
                                        {{ $app->app_key }}
                                    </span>
                                </div>
                                {{-- Usage bar --}}
                                <div class="mt-1 h-1 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-1 bg-purple-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </x-pulse::td>
                            <x-pulse::td class="text-right tabular-nums font-semibold text-gray-800 dark:text-gray-100">
                                {{ number_format($app->count) }}
                            </x-pulse::td>
                            <x-pulse::td class="text-right tabular-nums text-gray-500 dark:text-gray-400 text-sm">
                                {{ $pct }}%
                            </x-pulse::td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse::table>
        @endif

        {{-- Daily plan-limit usage --}}
        @if ($dailyUsages->isNotEmpty())
            <div class="mt-4 px-4 pb-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    Today's Plan Usage
                </p>
                <div class="space-y-2">
                    @foreach ($dailyUsages as $usage)
                        @php
                            $pct   = (float) ($usage['pct'] ?? 0);
                            $color = $pct >= 90 ? 'bg-red-500'
                                   : ($pct >= 70 ? 'bg-yellow-500' : 'bg-green-500');
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-300 mb-0.5">
                                <span class="font-medium">{{ $usage['app_name'] ?? '–' }}</span>
                                <span>
                                    {{ number_format($usage['today_count'] ?? 0) }}
                                    / {{ number_format($usage['limit'] ?? 0) }}
                                    ({{ $pct }}%)
                                </span>
                            </div>
                            <div class="h-1.5 w-full bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-1.5 {{ $color }} rounded-full transition-all"
                                     style="width: {{ min($pct, 100) }}%"></div>
                            </div>
                            @if ($pct >= 100)
                                <p class="text-xs text-red-500 font-semibold mt-0.5">⚠ Daily limit reached</p>
                            @elseif ($pct >= 90)
                                <p class="text-xs text-yellow-600 font-semibold mt-0.5">⚠ Approaching daily limit</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

