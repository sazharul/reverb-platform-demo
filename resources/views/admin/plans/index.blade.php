@extends('layouts.dashboard')

@section('title', 'Subscription Plans')
@section('breadcrumb', 'Admin / Plans')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Subscription Plans</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Manage subscription plans for users.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="btn-primary">+ Create Plan</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Apps</th>
                    <th>Connections</th>
                    <th>Daily Messages</th>
                    <th>Webhooks</th>
                    <th>Any Channel</th>
                    <th>Subscribers</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td>
                            <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $plan->name }}</p>
                            <p class="text-xs text-neutral-400">{{ $plan->slug }}</p>
                        </td>
                        <td class="text-sm font-medium">${{ number_format($plan->price, 2) }}<span class="text-xs text-neutral-400">/{{ $plan->billing_cycle }}</span></td>
                        <td class="text-sm">{{ $plan->max_apps >= 999999 ? '∞' : $plan->max_apps }}</td>
                        <td class="text-sm">{{ $plan->max_connections_per_app >= 999999 ? '∞' : number_format($plan->max_connections_per_app) }}</td>
                        <td class="text-sm">{{ $plan->daily_message_limit >= 999999 ? '∞' : number_format($plan->daily_message_limit) }}</td>
                        <td>@if($plan->webhook_allowed)<span class="badge-green">Yes</span>@else<span class="badge-neutral">No</span>@endif</td>
                        <td>@if($plan->allow_any_channel)<span class="badge-green">Yes</span>@else<span class="badge-neutral">No</span>@endif</td>
                        <td><span class="badge-blue">{{ $plan->active_subscribers_count }}</span></td>
                        <td>@if($plan->is_active)<span class="badge-green">Active</span>@else<span class="badge-neutral">Inactive</span>@endif</td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-sm text-brand-600 hover:underline">Edit</a>
                                @if($plan->active_subscribers_count === 0)
                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Delete this plan?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

