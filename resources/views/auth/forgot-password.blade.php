<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Reset password</div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Forgot your password?</h1>
        <p class="text-sm text-zinc-500 mt-1">No problem. Drop your email and we'll send a reset link.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5"/>
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus
                          placeholder="you@example.com"/>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5"/>
        </div>

        <x-primary-button class="w-full">
            Send reset link
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </x-primary-button>

        <p class="text-[11px] text-zinc-500 text-center pt-2 border-t border-zinc-100">
            Remembered it?
            <a href="{{ route('login') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Back to login</a>
        </p>
    </form>
</x-guest-layout>
