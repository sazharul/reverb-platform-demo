<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount('activeSubscribers')
            ->orderBy('sort_order')
            ->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string',
            'price'                   => 'required|numeric|min:0',
            'billing_cycle'           => 'required|in:monthly,yearly,lifetime',
            'max_apps'                => 'required|integer|min:1',
            'max_connections_per_app' => 'required|integer|min:1',
            'daily_message_limit'     => 'required|integer|min:1',
            'max_channels_per_app'    => 'required|integer|min:1',
            'webhook_allowed'         => 'nullable|boolean',
            'allow_any_channel'       => 'nullable|boolean',
            'sort_order'              => 'nullable|integer|min:0',
        ]);

        $data['webhook_allowed']   = $request->boolean('webhook_allowed');
        $data['allow_any_channel'] = $request->boolean('allow_any_channel');

        SubscriptionPlan::create($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name'                    => 'required|string|max:100',
            'description'             => 'nullable|string',
            'price'                   => 'required|numeric|min:0',
            'billing_cycle'           => 'required|in:monthly,yearly,lifetime',
            'max_apps'                => 'required|integer|min:1',
            'max_connections_per_app' => 'required|integer|min:1',
            'daily_message_limit'     => 'required|integer|min:1',
            'max_channels_per_app'    => 'required|integer|min:1',
            'webhook_allowed'         => 'nullable|boolean',
            'allow_any_channel'       => 'nullable|boolean',
            'is_active'               => 'nullable|boolean',
            'sort_order'              => 'nullable|integer|min:0',
        ]);

        $data['webhook_allowed']   = $request->boolean('webhook_allowed');
        $data['allow_any_channel'] = $request->boolean('allow_any_channel');
        $data['is_active']         = $request->boolean('is_active');

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->activeSubscribers()->count() > 0) {
            return back()->with('error', 'Cannot delete a plan with active subscribers.');
        }
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }
}

