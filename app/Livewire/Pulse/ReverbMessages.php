<?php

namespace App\Livewire\Pulse;

use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class ReverbMessages extends Card
{
    /**
     * Render the Pulse card for Reverb message tracking.
     * Shown on the /pulse admin dashboard.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        [[$topApps, $totalMessages, $graph, $dailyUsages], $time, $runAt] = $this->remember(
            fn () => $this->fetchData()
        );

        return view('livewire.pulse.reverb-messages', [
            'topApps'       => $topApps,
            'totalMessages' => $totalMessages,
            'graph'         => $graph,
            'dailyUsages'   => $dailyUsages,
            'time'          => $time,
            'runAt'         => $runAt,
            'cols'          => $this->cols,
            'rows'          => $this->rows,
            'class'         => $this->class,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function fetchData(): array
    {
        $interval = $this->periodAsInterval();

        // Top apps by message count in the selected period
        $topApps = Pulse::aggregate(
            type: 'reverb_message',
            aggregates: 'count',
            interval: $interval,
            orderBy: 'count',
            direction: 'desc',
            limit: 20,
        )->map(function ($row) {
            $decoded = rescue(fn () => json_decode($row->key, true), []);
            return (object) [
                'app_id'   => $decoded['app_id'] ?? null,
                'app_key'  => $decoded['app_key'] ?? '?',
                'app_name' => $decoded['app_name'] ?? 'Unknown',
                'user_id'  => $decoded['user_id'] ?? null,
                'count'    => $row->count,
            ];
        });

        $totalMessages = $topApps->sum('count');

        // Graph data (time-series) for sparklines
        $graph = Pulse::graph(
            types: ['reverb_message'],
            aggregate: 'count',
            interval: $interval,
        );

        // Daily usage snapshots from Pulse::set()
        $dailyUsages = Pulse::values('reverb_daily_usage')
            ->map(fn ($entry) => rescue(fn () => json_decode($entry->value, true), []))
            ->filter()
            ->values();


        return [$topApps, $totalMessages, $graph, $dailyUsages];
    }

    public function periodOptions(): array
    {
        return [
            '5_minutes' => '5 minutes',
            '1_hour'    => '1 hour',
            '24_hours'  => '24 hours',
            '7_days'    => '7 days',
        ];
    }
}

