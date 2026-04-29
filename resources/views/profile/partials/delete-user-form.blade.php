<section class="space-y-5">
    <header>
        <div class="text-[10px] uppercase tracking-[0.18em] text-rose-500/80 font-semibold mb-1">Danger zone</div>
        <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Delete account</h2>
        <p class="mt-1 text-sm text-zinc-500">Permanently removes your account and all data. Download anything you want to keep first.</p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        {{ __('Delete account') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="text-[10px] uppercase tracking-[0.18em] text-rose-500/80 font-semibold mb-1">Confirm</div>
            <h2 class="text-lg font-semibold tracking-tight text-zinc-900">Are you sure?</h2>
            <p class="mt-1 text-sm text-zinc-500">This permanently deletes your account and data. Enter your password to confirm.</p>

            <div class="mt-5">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only"/>
                <x-text-input id="password" name="password" type="password" placeholder="{{ __('Password') }}" class="w-full"/>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5"/>
            </div>

            <div class="mt-5 flex items-center justify-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
                <x-danger-button>
                    {{ __('Delete account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
