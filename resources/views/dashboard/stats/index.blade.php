@extends('layouts.dashboard')

@section('title', 'Usage Stats')
@section('breadcrumb', 'Dashboard / Usage Stats')

@section('content')
    <div class="mb-5">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Usage Statistics</h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Analytics for the last 30 days across all your apps.</p>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <div class="stat-icon bg-brand-50 dark:bg-brand-900/20">
                <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Messages This Month</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($totalThisMonth) }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green-50 dark:bg-green-900/20">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Avg / Day</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($avgPerDay) }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-purple-50 dark:bg-purple-900/20">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Peak Day</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($peakDay) }}</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-yellow-50 dark:bg-yellow-900/20">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Active Channels (24h)</p>
                <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ number_format($activeChannels) }}</p>
            </div>
        </div>
    </div>

    {{-- Plan usage bar --}}
    <div class="card p-5 mb-6">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Plan Usage — {{ $usage['plan_name'] }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-neutral-500 dark:text-neutral-400">Apps</span>
                    <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $usage['apps_used'] }} / {{ $usage['apps_limit'] >= 999999 ? '∞' : $usage['apps_limit'] }}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-bar-fill bg-brand-600" style="width: {{ $usage['apps_limit'] >= 999999 ? 5 : min(100, ($usage['apps_used'] / max(1, $usage['apps_limit'])) * 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-neutral-500 dark:text-neutral-400">Messages Today</span>
                    <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ number_format($usage['messages_today']) }} / {{ $usage['messages_daily_limit'] >= 999999 ? '∞' : number_format($usage['messages_daily_limit']) }}</span>
                </div>
                <div class="progress-bar">
                    @php $msgPct = $usage['messages_daily_limit'] >= 999999 ? 2 : min(100, ($usage['messages_today'] / max(1, $usage['messages_daily_limit'])) * 100); @endphp
                    <div class="progress-bar-fill {{ $msgPct > 80 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $msgPct }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-neutral-500 dark:text-neutral-400">Max Connections / App</span>
                    <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $usage['max_connections'] >= 999999 ? '∞' : number_format($usage['max_connections']) }}</span>
                </div>
                <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">
                    Webhooks: {!! $usage['webhook_allowed'] ? '<span class="text-green-600">Enabled</span>' : '<span class="text-neutral-400">Not included</span>' !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">
        <div class="xl:col-span-2 card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Messages / Day (Last 30 Days)</h3>
            <div style="position:relative; height:300px;">
                <canvas id="messagesChart"></canvas>
            </div>
        </div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">By Status</h3>
            <div style="position:relative; height:300px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    @if(count($perAppStats) > 0)
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white mb-4">Messages by App (30 Days)</h3>
            <div style="position:relative; height:280px;">
                <canvas id="appsChart"></canvas>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;

    // Messages / Day
    new Chart(document.getElementById('messagesChart'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Messages',
                data: @json($chartData),
                borderColor: '#005fa2',
                backgroundColor: 'rgba(0,95,162,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor } },
                x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } }
            }
        }
    });

    // Status doughnut
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($statusBreakdown)) !!},
            datasets: [{
                data: {!! json_encode(array_values($statusBreakdown)) !!},
                backgroundColor: ['#22c55e', '#ef4444', '#eab308'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Per-app bar
    @if(count($perAppStats) > 0)
    new Chart(document.getElementById('appsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($perAppStats)) !!},
            datasets: [{
                label: 'Messages',
                data: {!! json_encode(array_values($perAppStats)) !!},
                backgroundColor: '#005fa2',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor } },
                x: { grid: { display: false } }
            }
        }
    });
    @endif
</script>
@endpush

