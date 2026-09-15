<?php

namespace App\Livewire\Pulse;

use Carbon\CarbonInterval;
use Laravel\Pulse\Facades\Pulse;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;

#[Lazy]
class ReverbChannels extends Card
{
    public function render(): \Illuminate\Contracts\View\View
    {
        [$topChannels, $time, $runAt] = $this->remember(fn () => $this->fetchData());

        return view('livewire.pulse.reverb-channels', [
            'topChannels' => $topChannels,
            'time'        => $time,
            'runAt'       => $runAt,
            'cols'        => $this->cols,
            'rows'        => $this->rows,
            'class'       => $this->class,
        ]);
    }

    private function fetchData(): \Illuminate\Support\Collection
    {
        $interval = $this->periodAsInterval();

        $topChannels = Pulse::aggregate(
            type: 'reverb_channel',
            aggregates: 'count',
            interval: $interval,
            orderBy: 'count',
            direction: 'desc',
            limit: 15,
        )->map(function ($row) {
            $decoded = rescue(fn () => json_decode($row->key, true), []);
            return (object) [
                'app_id'  => $decoded['app_id'] ?? null,
                'channel' => $decoded['channel'] ?? '?',
                'user_id' => $decoded['user_id'] ?? null,
                'count'   => $row->count,
            ];
        });

        return $topChannels;
    }

    public function periodOptions()
    {
        return [
            '5_minutes' => '5 minutes',
            '1_hour' => '1 hour',
            '24_hours' => '24 hours',
            '7_days' => '7 days',
        ];
    }
}

