<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Laravel Pulse ──────────────────────────────────────────────────────────
// Trim old data weekly to keep the Pulse tables lean.
Schedule::command('pulse:clear --type=pulse_aggregates --force')->weekly();
