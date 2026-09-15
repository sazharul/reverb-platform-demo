<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\PlanLimitService;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $usage = app(PlanLimitService::class)->usageSummary(auth()->user());
        $currentPlanName = $usage['plan_name'];

        return view('dashboard.plans.index', compact('plans', 'usage', 'currentPlanName'));
    }
}

