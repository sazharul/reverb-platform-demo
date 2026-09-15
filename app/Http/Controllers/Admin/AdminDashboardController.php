<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\EventLog;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'     => User::where('user_type', 'user')->count(),
            'active_users'    => User::where('user_type', 'user')->where('is_active', true)->count(),
            'total_apps'      => App::count(),
            'active_apps'     => App::where('is_active', true)->count(),
            'total_events'    => EventLog::count(),
            'delivered_today' => EventLog::where('status', 'delivered')->whereDate('created_at', today())->count(),
            'failed_events'   => EventLog::where('status', 'failed')->count(),
            'pending_events'  => EventLog::where('status', 'pending')->count(),
        ];

        $recentUsers = User::where('user_type', 'user')
            ->withCount('apps')
            ->latest()
            ->limit(6)
            ->get();

        $recentEvents = EventLog::with('app.user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentEvents'));
    }
}