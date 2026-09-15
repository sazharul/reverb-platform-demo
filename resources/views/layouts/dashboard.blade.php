<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-100 dark:bg-neutral-950">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ───────────────────────────────────────────── --}}
    <aside class="w-64 flex-shrink-0 flex flex-col
                      bg-white dark:bg-neutral-900
                      border-r border-neutral-200 dark:border-neutral-800
                      overflow-y-auto scrollbar-hide">

        {{-- Logo --}}
        <div class="h-16 flex items-center px-6 border-b border-neutral-200 dark:border-neutral-800 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                    <span class="text-white font-bold text-sm">EX</span>
                </div>
                <span class="font-semibold text-neutral-900 dark:text-white text-sm">
                        {{ $siteName }}
                    </span>
            </a>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @if(auth()->user()->isAdminType())
                @include('layouts.partials.admin-nav')
            @else
                @include('layouts.partials.user-nav')
            @endif
        </nav>

        {{-- User footer --}}
        <div class="p-3 border-t border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-neutral-900 dark:text-white truncate">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                        {{ auth()->user()->email }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-neutral-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                            title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main area ─────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="h-16 flex-shrink-0 flex items-center justify-between px-6
                           bg-white dark:bg-neutral-900
                           border-b border-neutral-200 dark:border-neutral-800">
            <div>
                <h1 class="text-sm font-semibold text-neutral-900 dark:text-white">
                    @yield('title', 'Dashboard')
                </h1>
                @hasSection('breadcrumb')
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        @yield('breadcrumb')
                    </p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                {{-- Theme toggle --}}
                <button onclick="toggleTheme()"
                        class="w-8 h-8 flex items-center justify-center rounded-lg
                               text-neutral-500 dark:text-neutral-400
                               hover:bg-neutral-100 dark:hover:bg-neutral-800
                               transition-colors">
                    {{-- Sun --}}
                    <svg class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    {{-- Moon --}}
                    <svg class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- Role badge --}}
                <span class="text-xs text-neutral-500 dark:text-neutral-400">You are logged in as</span>
                @if(auth()->user()->isSuperAdmin())
                    <span class="badge-blue">Superadmin</span>
                @elseif(auth()->user()->isAdminType())
                    <span class="badge-neutral">{{ auth()->user()->getRoleNames()->first() }}</span>
                @else
                    <span class="badge-green">User</span>
                @endif
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert-success mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                              clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                              clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>