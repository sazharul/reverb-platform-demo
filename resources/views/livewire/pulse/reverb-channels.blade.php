<x-pulse::card :cols="$cols ?? 4" :rows="$rows ?? 2" :class="$class ?? ''">
    <x-pulse::card-header name="Reverb Channels">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
            </svg>
        </x-slot:icon>
        <x-slot:after>
            <x-pulse::select wire:model.live="period" label="Period" :options="$this->periodOptions()"
                             @change="period = $event.target.value" />
        </x-slot:after>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand ?? false">
        @if ($topChannels->isEmpty())
            <x-pulse::no-results>No channel data recorded yet.</x-pulse::no-results>
        @else
            <x-pulse::table>
                <colgroup>
                    <col />
                    <col class="w-20" />
                </colgroup>
                <x-pulse::thead>
                    <tr>
                        <x-pulse::th>Channel</x-pulse::th>
                        <x-pulse::th class="text-right">Events</x-pulse::th>
                    </tr>
                </x-pulse::thead>
                <tbody>
                    @foreach ($topChannels as $ch)
                        <tr wire:key="ch-{{ $ch->app_id }}-{{ $ch->channel }}">
                            <x-pulse::td>
                                <span class="font-mono text-xs text-gray-700 dark:text-gray-300 truncate max-w-[220px] block"
                                      title="{{ $ch->channel }}">
                                    {{ $ch->channel }}
                                </span>
                            </x-pulse::td>
                            <x-pulse::td class="text-right tabular-nums font-semibold text-gray-800 dark:text-gray-100">
                                {{ number_format($ch->count) }}
                            </x-pulse::td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse::table>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

