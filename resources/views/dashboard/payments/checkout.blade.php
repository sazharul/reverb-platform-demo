@extends('layouts.dashboard')

@section('title', 'Checkout — ' . $plan->name)
@section('breadcrumb', 'Dashboard / Plans / Checkout')

@section('content')
    <div class="w-full">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Confirm Your Subscription</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">You are upgrading to the <strong>{{ $plan->name }}</strong> plan.</p>
        </div>

        <div class="card p-6 mb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $plan->name }} Plan</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $plan->description }}</p>
                </div>
                <div class="text-right">
                    <span class="text-3xl font-bold text-neutral-900 dark:text-white">৳{{ number_format($plan->price, 2) }}</span>
                    <span class="text-sm text-neutral-500 dark:text-neutral-400">/{{ $plan->billing_cycle }}</span>
                </div>
            </div>

            <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4 mb-6">
                <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Plan Features</h4>
                <ul class="space-y-2 text-sm">
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
            </div>

            <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">Payment Details</h4>
                <form action="{{ route('user.payments.initiate', $plan) }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Name</label>
                            <input type="text" class="input" value="{{ auth()->user()->name }}" disabled>
                        </div>
                        <div>
                            <label class="label">Email</label>
                            <input type="text" class="input" value="{{ auth()->user()->email }}" disabled>
                        </div>
                    </div>

                    <div>
                        <label for="phone" class="label">Phone Number <span class="text-red-500">*</span></label>
                        <input id="phone" name="phone" type="text" class="input @error('phone') input-error @enderror"
                               value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Required by payment gateway.</p>
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-neutral-600 dark:text-neutral-400">{{ $plan->name }} Plan ({{ ucfirst($plan->billing_cycle) }})</span>
                            <span class="text-neutral-900 dark:text-white font-medium">৳{{ number_format($plan->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold border-t border-neutral-200 dark:border-neutral-700 pt-2 mt-2">
                            <span class="text-neutral-900 dark:text-white">Total</span>
                            <span class="text-neutral-900 dark:text-white">৳{{ number_format($plan->price, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="btn-primary flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Pay ৳{{ number_format($plan->price, 2) }} via SSLCOMMERZ
                        </button>
                        <a href="{{ route('user.plans.index') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Cancel</a>
                    </div>

                    <p class="text-xs text-neutral-400 dark:text-neutral-500">
                        You will be redirected to SSLCOMMERZ secure payment gateway. Supports bKash, Nagad, Rocket, Visa, Mastercard, and more.
                    </p>
                </form>
            </div>
        </div>

        @if($currentPlan && $currentPlan->name !== 'Free')
            <div class="text-center">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    Your current <strong>{{ $currentPlan->name }}</strong> plan will be replaced upon successful payment.
                </p>
            </div>
        @endif
    </div>
@endsection

