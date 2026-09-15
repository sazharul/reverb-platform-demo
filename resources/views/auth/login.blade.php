<x-app-layout>
    <div class="min-h-screen flex">

        {{-- Left brand panel --}}
        <div class="hidden lg:flex lg:w-1/2 bg-brand-600 flex-col justify-between p-12">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <span class="text-white font-bold">EX</span>
                </div>
                <span class="text-white font-semibold text-lg">{{ $siteName }}</span>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-white leading-tight">
                    Real-time events,<br>built for scale.
                </h2>
                <p class="text-brand-200 mt-3 text-sm leading-relaxed">
                    Connect your applications with WebSocket infrastructure
                    that delivers every event with zero failure.
                </p>
            </div>
            <p class="text-brand-300 text-xs">© {{ date('Y') }} {{ $siteName }}</p>
        </div>

        {{-- Right login form --}}
        <div class="flex-1 flex items-center justify-center p-8 bg-white dark:bg-neutral-950">
            <div class="w-full max-w-sm">

                {{-- Mobile logo --}}
                <div class="flex items-center gap-2 mb-8 lg:hidden">
                    <div class="w-8 h-8 rounded-lg bg-brand-600 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">R</span>
                    </div>
                    <span class="font-semibold text-neutral-900 dark:text-white">{{ $siteName }}</span>
                </div>

                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Welcome back</h1>
                <p class="text-neutral-500 dark:text-neutral-400 text-sm mt-1 mb-8">
                    Sign in to your account
                </p>

                @if($errors->any())
                    <div class="alert-error mb-5">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="label" for="email">Email address</label>
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                               placeholder="you@example.com"
                               required autofocus autocomplete="email">
                        @error('email')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="label mb-0" for="password">Password</label>
                        </div>
                        <input id="password" name="password" type="password"
                               class="input {{ $errors->has('password') ? 'input-error' : '' }}"
                               placeholder="••••••••"
                               required autocomplete="current-password">
                        @error('password')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember"
                                   class="rounded border-neutral-300 dark:border-neutral-600
                                          text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-neutral-600 dark:text-neutral-400">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        Sign in
                    </button>
                </form>

                <p class="text-center text-sm text-neutral-500 dark:text-neutral-400 mt-6">
                    Don't have an account?
                    <a href="{{ route('register') }}"
                       class="text-brand-600 dark:text-brand-400 font-medium hover:underline">
                        Create one
                    </a>
                </p>

                {{-- Theme toggle --}}
                <div class="flex justify-center mt-8">
                    <button onclick="toggleTheme()"
                            class="text-xs text-neutral-400 hover:text-neutral-600
                               dark:hover:text-neutral-300 flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                        </svg>
                        <svg class="w-3.5 h-3.5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        Toggle theme
                    </button>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>