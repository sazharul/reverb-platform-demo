@extends('layouts.dashboard')

@section('title', 'Payment History')
@section('breadcrumb', 'Dashboard / Payments')

@section('content')
    <div class="w-full">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Payment History</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">All your past transactions.</p>
            </div>
            <a href="{{ route('user.plans.index') }}" class="btn-secondary">View Plans</a>
        </div>

        <div class="card overflow-hidden">
            @if($payments->isEmpty())
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No payments yet.</p>
                    <a href="{{ route('user.plans.index') }}" class="btn-primary mt-4 inline-block">Browse Plans</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>
                                    <span class="font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ $payment->transaction_id }}</span>
                                </td>
                                <td class="text-sm">{{ $payment->plan->name ?? 'Deleted Plan' }}</td>
                                <td class="text-sm font-medium">৳{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-xs text-neutral-500">{{ $payment->payment_method ?? '—' }}</td>
                            <td>
                                @if($payment->status === 'completed')
                                    <span class="badge-green">✓ Confirmed</span>
                                @elseif($payment->status === 'pending')
                                    <span class="badge-yellow">Pending</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge-red">Failed</span>
                                @elseif($payment->status === 'cancelled')
                                    <span class="badge-neutral">Cancelled</span>
                                @else
                                    <span class="badge-neutral">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                                <td class="text-xs text-neutral-400 whitespace-nowrap">
                                    {{ $payment->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="text-right">
                                    @if($payment->isCompleted())
                                        <a href="{{ route('user.payments.receipt', $payment) }}" class="text-sm text-brand-600 hover:underline">Receipt</a>
                                    @else
                                        <span class="text-xs text-neutral-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

