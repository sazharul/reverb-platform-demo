@extends('layouts.dashboard')

@section('title', 'Plans & Pricing')
@section('breadcrumb', 'Dashboard / Plans')

@section('content')
    <div class="w-full">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Choose Your Plan</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">Scale your real-time messaging as you grow. Current plan: <strong>{{ $currentPlanName }}</strong></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-{{ min(count($plans), 4) }} gap-5">
            @foreach($plans as $plan)
                @php $isCurrent = $plan->name === $currentPlanName; @endphp
                <div class="card p-6 flex flex-col {{ $isCurrent ? 'ring-2 ring-brand-600 dark:ring-brand-400' : '' }}">
                    @if($isCurrent)
                        <span class="badge-blue self-start mb-3">Current Plan</span>
                    @endif
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $plan->name }}</h3>
                    <div class="mt-2 mb-4">
                        @if($plan->isFree())
                            <span class="text-3xl font-bold text-neutral-900 dark:text-white">Free</span>
                        @else
                            <span class="text-3xl font-bold text-neutral-900 dark:text-white">৳{{ number_format($plan->price, 2) }}</span>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">/{{ $plan->billing_cycle }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-5">{{ $plan->description }}</p>

                    <ul class="space-y-2 text-sm flex-1 mb-6">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $plan->max_apps >= 999999 ? 'Unlimited' : $plan->max_apps }} apps</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $plan->max_connections_per_app >= 999999 ? 'Unlimited' : number_format($plan->max_connections_per_app) }} connections / app</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $plan->daily_message_limit >= 999999 ? 'Unlimited' : number_format($plan->daily_message_limit) }} messages / day</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span>{{ $plan->max_channels_per_app >= 999999 ? 'Unlimited' : $plan->max_channels_per_app }} channels / app</span>
                        </li>
                        <li class="flex items-center gap-2">
                            @if($plan->webhook_allowed)
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>Webhook support</span>
                            @else
                                <svg class="w-4 h-4 text-neutral-300 dark:text-neutral-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                <span class="text-neutral-400">No webhooks</span>
                            @endif
                        </li>
                    </ul>

                    @if($isCurrent)
                        <button disabled class="btn-secondary w-full opacity-60">Current Plan</button>
                    @elseif($plan->isFree())
                        <form action="{{ route('user.payments.subscribe-free', $plan) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-secondary w-full">Switch to Free</button>
                        </form>
                    @else
                        <a href="{{ route('user.payments.checkout', $plan) }}" class="btn-primary w-full text-center">
                            Subscribe — ৳{{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-center text-xs text-neutral-400 dark:text-neutral-500 mt-6">
            Payments processed securely via SSLCOMMERZ. Supports bKash, Nagad, Rocket, Visa, Mastercard & more.
        </p>

        @if(auth()->user()->payments()->exists())
            <div class="text-center mt-3">
                <a href="{{ route('user.payments.history') }}" class="text-sm text-brand-600 hover:underline">View Payment History →</a>
            </div>
        @endif
    </div>
@endsection

