<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Sign in</div>
        {{-- Bumped to font-bold (700) per LOGIN_GLASS_CHECKLIST §3.1 — the
             heading needs to anchor against video motion behind the glass card. --}}
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Welcome back</h1>
        <p class="text-sm text-zinc-500 mt-1">Pick up where you left off in the console.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5"/>
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                          placeholder="you@example.com"/>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5"/>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')"/>
                @if (Route::has('password.request'))
                    <a class="text-[11px] text-emerald-700 hover:text-emerald-800 font-medium" href="{{ route('password.request') }}">
                        Forgot it?
                    </a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password"
                          placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5"/>
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-zinc-600">
            <input id="remember_me" type="checkbox" name="remember"
                   class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
            Keep me signed in
        </label>

        <x-primary-button class="w-full">
            {{ __('Log in') }}
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </x-primary-button>

        @if (Route::has('register'))
            <p class="text-[11px] text-zinc-500 text-center pt-2 border-t border-zinc-100">
                Need an account?
                <a href="{{ route('register') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Request access</a>
            </p>
        @endif
    </form>
</x-guest-layout>
