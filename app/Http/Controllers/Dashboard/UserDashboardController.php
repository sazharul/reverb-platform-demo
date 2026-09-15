<?php
// app/Http/Controllers/Dashboard/UserDashboardController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\EventLog;
use App\Services\PlanLimitService;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'total_apps'       => App::where('user_id', $user->id)->count(),
            'active_apps'      => App::where('user_id', $user->id)->where('is_active', true)->count(),
            'total_events'     => EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))->count(),
            'delivered_today'  => EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'delivered')
                ->whereDate('created_at', today())
                ->count(),
            'failed_events'    => EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'failed')
                ->count(),
            'pending_events'   => EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'pending')
                ->count(),
        ];

        $recentEvents = EventLog::whereHas('app', fn($q) => $q->where('user_id', $user->id))
            ->with('app')
            ->latest()
            ->limit(8)
            ->get();

        $apps = App::where('user_id', $user->id)
            ->withCount('eventLogs')
            ->latest()
            ->limit(5)
            ->get();

        $usage = app(PlanLimitService::class)->usageSummary($user);

        return view('dashboard.index', compact('stats', 'recentEvents', 'apps', 'usage'));
    }
}