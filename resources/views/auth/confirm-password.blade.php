<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Secure area</div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Confirm your password</h1>
        <p class="text-sm text-zinc-500 mt-1">This area handles sensitive settings. Please re-enter your password to continue.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" class="mb-1.5"/>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" autofocus placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5"/>
        </div>

        <x-primary-button class="w-full">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
