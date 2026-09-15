@extends('layouts.dashboard')

@section('title', 'Payment Receipt')
@section('breadcrumb', 'Dashboard / Payments / Receipt')

@section('content')
    <div class="w-full">
        <div class="text-center mb-8">
            @if($payment->isCompleted())
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Payment Confirmed!</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">Your subscription has been activated successfully.</p>
            @else
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-neutral-900 dark:text-white">Payment {{ ucfirst($payment->status) }}</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-2">Transaction: {{ $payment->transaction_id }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Receipt Details</h3>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Transaction ID</span>
                        <span class="text-sm font-mono text-neutral-900 dark:text-white">{{ $payment->transaction_id }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Plan</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->plan->name ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Billing Cycle</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ ucfirst($payment->plan->billing_cycle ?? '-') }}</span>
                    </div>

                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Amount Paid</span>
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">৳{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</span>
                    </div>

                    @if($payment->payment_method)
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Payment Method</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->payment_method }}</span>
                    </div>
                    @endif

                    @if($payment->bank_tran_id)
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Bank Transaction</span>
                        <span class="text-sm font-mono text-neutral-900 dark:text-white">{{ $payment->bank_tran_id }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Status</span>
                        @if($payment->isCompleted())
                            <span class="badge-green">✓ Confirmed</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge-yellow">Pending</span>
                        @elseif($payment->status === 'failed')
                            <span class="badge-red">Failed</span>
                        @else
                            <span class="badge-neutral">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </div>

                    @if($payment->paid_at)
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Paid At</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->paid_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($payment->isCompleted() && $payment->plan)
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Your Active Plan</h3>
                @php $sub = auth()->user()->activeSubscription; @endphp
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Plan</span>
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $payment->plan->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Max Apps</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->plan->max_apps >= 999999 ? 'Unlimited' : $payment->plan->max_apps }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Max Connections</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->plan->max_connections_per_app >= 999999 ? 'Unlimited' : number_format($payment->plan->max_connections_per_app) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-neutral-100 dark:border-neutral-800">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Daily Messages</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $payment->plan->daily_message_limit >= 999999 ? 'Unlimited' : number_format($payment->plan->daily_message_limit) }}</span>
                    </div>
                    @if($sub && $sub->expires_at)
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">Expires</span>
                        <span class="text-sm text-neutral-900 dark:text-white">{{ $sub->expires_at->format('d M Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 mt-6">
            <a href="{{ route('dashboard') }}" class="btn-primary">Go to Dashboard</a>
            <a href="{{ route('user.apps.create') }}" class="btn-secondary">Create an App</a>
            <a href="{{ route('user.payments.history') }}" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Payment History</a>
        </div>
    </div>
@endsection

