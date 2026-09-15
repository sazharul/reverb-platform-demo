<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\App as ReverbApp;
use App\Models\EventLog;
use App\Services\PlanLimitService;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;
use Laravel\Pulse\Facades\Pulse;

class StatsController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $appIds = ReverbApp::where('user_id', $user->id)->pluck('id');

        // ── 30-day daily message chart (EventLog — authoritative) ─────────────
        $dailyMessages = EventLog::whereIn('app_id', $appIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $chartLabels = [];
        $chartData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->format('M d');
            $chartData[]   = $dailyMessages[$date] ?? 0;
        }

        // ── Per-app 30-day breakdown ───────────────────────────────────────────
        $perAppStats = EventLog::whereIn('event_logs.app_id', $appIds)
            ->where('event_logs.created_at', '>=', now()->subDays(30))
            ->join('apps', 'apps.id', '=', 'event_logs.app_id')
            ->select('apps.name', DB::raw('COUNT(*) as count'))
            ->groupBy('apps.name')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'name')
            ->toArray();

        // ── Status breakdown ──────────────────────────────────────────────────
        $statusBreakdown = EventLog::whereIn('app_id', $appIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Summary numbers ───────────────────────────────────────────────────
        $totalThisMonth = EventLog::whereIn('app_id', $appIds)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $avgPerDay     = count($chartData) > 0 ? round(array_sum($chartData) / 30) : 0;
        $peakDay       = count($chartData) > 0 ? max($chartData) : 0;
        $activeChannels = EventLog::whereIn('app_id', $appIds)
            ->where('created_at', '>=', now()->subDay())
            ->distinct('channel')
            ->count('channel');

        // ── Plan usage (fast, cache-based) + per-app breakdown ────────────────
        $limiter     = app(PlanLimitService::class);
        $usage       = $limiter->usageSummary($user);
        $perAppUsage = $limiter->perAppUsageSummary($user);

        // ── Pulse hourly rates (last 24 h, grouped into 24 buckets) ──────────
        // Used to show a real-time "recent activity" line on the stats page.
        $pulseHourly = $this->buildPulseHourlyData($appIds->all());

        return view('dashboard.stats.index', compact(
            'chartLabels', 'chartData', 'perAppStats', 'statusBreakdown',
            'totalThisMonth', 'avgPerDay', 'peakDay', 'activeChannels',
            'usage', 'perAppUsage', 'pulseHourly'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build 24-hour hourly bucket data from pulse_aggregates for this user's apps.
     * Returns ['labels' => [...], 'data' => [...]] suitable for Chart.js.
     */
    private function buildPulseHourlyData(array $appIds): array
    {
        if (empty($appIds)) {
            return ['labels' => [], 'data' => []];
        }

        try {
            // Query pulse_aggregates directly for reverb_message counts in the last 24 h
            $rows = DB::table('pulse_aggregates')
                ->where('type', 'reverb_message')
                ->where('aggregate', 'count')
                ->where('period', 60) // 60-minute buckets
                ->where('bucket', '>=', now()->subHours(24)->timestamp)
                ->get(['bucket', 'key', 'value']);

            // Filter to only this user's apps by decoding the key
            $hourly = [];
            foreach ($rows as $row) {
                $decoded = rescue(fn () => json_decode($row->key, true), []);
                if (!in_array($decoded['app_id'] ?? null, $appIds)) continue;

                $hour = date('H:00', (int) $row->bucket);
                $hourly[$hour] = ($hourly[$hour] ?? 0) + (int) $row->value;
            }

            // Fill all 24 hours
            $labels = [];
            $data   = [];
            for ($i = 23; $i >= 0; $i--) {
                $label    = now()->subHours($i)->format('H:00');
                $labels[] = $label;
                $data[]   = $hourly[$label] ?? 0;
            }

            return ['labels' => $labels, 'data' => $data];
        } catch (\Throwable) {
            return ['labels' => [], 'data' => []];
        }
    }
}
