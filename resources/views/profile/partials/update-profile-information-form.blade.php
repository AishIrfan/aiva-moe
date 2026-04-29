<section>
    <header class="mb-5">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1">Profile information</div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Update name & email</h2>
        <p class="mt-1 text-sm text-zinc-500">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" class="mb-1.5"/>
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name"/>
            <x-input-error class="mt-1.5" :messages="$errors->get('name')"/>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="mb-1.5"/>
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username"/>
            <x-input-error class="mt-1.5" :messages="$errors->get('email')"/>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <div class="font-medium mb-0.5">Email is unverified</div>
                    <button form="send-verification" class="text-amber-700 hover:text-amber-900 underline underline-offset-4">
                        Re-send verification email
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-1.5 text-emerald-700 font-medium">A new verification link has been sent.</div>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 pt-1">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-xs text-emerald-700 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
