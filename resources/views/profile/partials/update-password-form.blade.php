<section>
    <header class="mb-5">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1">Security</div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Update password</h2>
        <p class="mt-1 text-sm text-zinc-500">Use a long, hard-to-guess password — a passphrase works well.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current password')" class="mb-1.5"/>
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New password')" class="mb-1.5"/>
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5"/>
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm new password')" class="mb-1.5"/>
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••"/>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5"/>
        </div>

        <div class="flex items-center gap-3 pt-1">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-xs text-emerald-700 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
