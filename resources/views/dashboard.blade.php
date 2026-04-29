<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold tracking-tight text-zinc-900">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-6 text-sm text-zinc-700">
                {{ __("You're logged in.") }}
            </div>
        </div>
    </div>
</x-app-layout>
