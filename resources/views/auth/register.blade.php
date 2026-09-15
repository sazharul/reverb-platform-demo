<x-app-layout>
    <div class="min-h-screen flex items-center justify-center
                bg-neutral-50 dark:bg-neutral-950 p-4">
        <div class="w-full max-w-md">

            <div class="flex items-center gap-2 mb-8 justify-center">
                <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center">
                    <span class="text-white font-bold">EX</span>
                </div>
                <span class="font-semibold text-neutral-900 dark:text-white text-lg">{{ $siteName }}</span>
            </div>

            <div class="card">
                <div class="card-header">
                    <h1 class="text-lg font-semibold text-neutral-900 dark:text-white">Create your account</h1>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                        Start integrating real-time events today
                    </p>
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert-error mb-5">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="label" for="name">Full name</label>
                            <input id="name" name="name" type="text"
                                   value="{{ old('name') }}"
                                   class="input {{ $errors->has('name') ? 'input-error' : '' }}"
                                   placeholder="John Doe"
                                   required autofocus autocomplete="name">
                            @error('name')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label" for="email">Email address</label>
                            <input id="email" name="email" type="email"
                                   value="{{ old('email') }}"
                                   class="input {{ $errors->has('email') ? 'input-error' : '' }}"
                                   placeholder="you@example.com"
                                   required autocomplete="email">
                            @error('email')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label" for="password">Password</label>
                            <input id="password" name="password" type="password"
                                   class="input {{ $errors->has('password') ? 'input-error' : '' }}"
                                   placeholder="Min. 8 characters"
                                   required autocomplete="new-password">
                            @error('password')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label" for="password_confirmation">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   class="input"
                                   placeholder="••••••••"
                                   required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn-primary w-full mt-2">
                            Create account
                        </button>
                    </form>

                    <p class="text-center text-sm text-neutral-500 dark:text-neutral-400 mt-5">
                        Already have an account?
                        <a href="{{ route('login') }}"
                           class="text-brand-600 dark:text-brand-400 font-medium hover:underline">
                            Sign in
                        </a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>