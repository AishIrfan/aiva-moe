<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Reset password</div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Choose a new password</h1>
        <p class="text-sm text-zinc-500 mt-1">Pick something memorable but not obvious.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5"/>
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username"/>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" class="mb-1.5"/>
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" class="mb-1.5"/>
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5"/>
        </div>

        <x-primary-button class="w-full">
            {{ __('Reset password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
