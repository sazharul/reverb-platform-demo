<p class="nav-section-title">Overview</p>

<a href="{{ route('admin.dashboard') }}"
   class="nav-link group {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">
    <span class="nav-link-icon">
        <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
    </span>
    Dashboard
</a>

<p class="nav-section-title mt-3">Management</p>

@can('users.view')
    <a href="{{ route('admin.users.index') }}"
       class="nav-link group {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </span>
        Users
    </a>
@endcan

@can('apps.view')
    <a href="{{ route('admin.apps.index') }}"
       class="nav-link group {{ request()->routeIs('admin.apps.*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </span>
        Apps
    </a>
@endcan

@can('events.view')
    <a href="{{ route('admin.events.index') }}"
       class="nav-link group {{ request()->routeIs('admin.events.*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </span>
        Event Logs
    </a>
@endcan

@if(auth()->user()->isSuperAdmin())
    <p class="nav-section-title mt-3">System</p>

    <a href="{{ url('/pulse') }}"
       class="nav-link group {{ request()->is('pulse*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </span>
        Pulse Dashboard
    </a>

    <a href="{{ route('admin.plans.index') }}"
       class="nav-link group {{ request()->routeIs('admin.plans.*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
        </span>
        Subscription Plans
    </a>

    <a href="{{ route('admin.roles.index') }}"
       class="nav-link group {{ request()->routeIs('admin.roles.*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </span>
        Roles & Permissions
    </a>

    <a href="{{ route('admin.settings') }}"
       class="nav-link group {{ request()->routeIs('admin.settings*') ? 'nav-link-active' : '' }}">
        <span class="nav-link-icon">
            <svg class="w-full h-full" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </span>
        System Settings
    </a>
@endif