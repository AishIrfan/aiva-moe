<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Sign up</div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Request access</h1>
        <p class="text-sm text-zinc-500 mt-1">Tell us who you are. We'll get you into the console.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" class="mb-1.5"/>
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                          placeholder="Cikgu Siti binti Abdullah"/>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5"/>
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username"
                          placeholder="you@school.edu.my"/>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="mb-1.5"/>
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" class="mb-1.5"/>
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5"/>
        </div>

        <x-primary-button class="w-full">
            {{ __('Register') }}
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </x-primary-button>

        <p class="text-[11px] text-zinc-500 text-center pt-2 border-t border-zinc-100">
            Already registered?
            <a href="{{ route('login') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Log in</a>
        </p>
    </form>
</x-guest-layout>
