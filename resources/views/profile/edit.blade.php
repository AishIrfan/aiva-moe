<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1">Account</div>
            <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ __('Profile') }}</h2>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto space-y-3">

            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-6 sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-6 sm:p-8">
                @include('profile.partials.update-password-form')
            </div>

            <div class="bg-white border border-rose-200/60 rounded-xl shadow-card p-6 sm:p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
