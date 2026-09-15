<p class="nav-section-title">Overview</p>

<a href="{{ route('dashboard') }}"
   class="nav-link group {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </span>
    Overview
</a>

<p class="nav-section-title mt-3">Realtime</p>

<a href="{{ route('user.apps.index') }}"
   class="nav-link group {{ request()->routeIs('user.apps.*') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
    </span>
    My Apps
</a>

<a href="{{ route('user.channels.index') }}"
   class="nav-link group {{ request()->routeIs('user.channels.*') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
        </svg>
    </span>
    Channels
</a>

<a href="{{ route('user.events.index') }}"
   class="nav-link group {{ request()->routeIs('user.events.*') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
    </span>
    Event Logs
</a>

<p class="nav-section-title mt-3">Insights</p>

<a href="{{ route('user.stats') }}"
   class="nav-link group {{ request()->routeIs('user.stats') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
    </span>
    Usage Stats
</a>

<p class="nav-section-title mt-3">Resources</p>

<a href="{{ route('user.docs') }}"
   class="nav-link group {{ request()->routeIs('user.docs') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
    </span>
    Documentation
</a>

<a href="{{ route('user.plans.index') }}"
   class="nav-link group {{ request()->routeIs('user.plans.*') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
    </span>
    Plans & Pricing
</a>

<a href="{{ route('user.payments.history') }}"
   class="nav-link group {{ request()->routeIs('user.payments.*') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
    </span>
    Payment History
</a>

<div class="pt-3 mt-3 border-t border-neutral-200 dark:border-neutral-800">
    <p class="nav-section-title pt-0">Account</p>
    <a href="{{ route('user.settings') }}"
       class="nav-link group {{ request()->routeIs('user.settings*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </span>
        Settings
    </a>
</div>