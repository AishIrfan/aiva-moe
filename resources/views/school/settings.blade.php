@extends('layouts.shell')
@section('title', 'School settings')
@section('subtitle', 'thresholds · notifications · integrations')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Settings</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Tune <span class="text-zinc-400">how alerts and integrations behave.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

    {{-- Notifications --}}
    <x-card title="Notifications" subtitle="how parents & staff are reached">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[email]" value="1"
                       @checked($settings['notifications']['email'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">Email</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Send alerts and digests via SMTP.</div>
                </div>
            </label>
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[sms]" value="1"
                       @checked($settings['notifications']['sms'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">SMS</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Critical alerts only — uses your provider quota.</div>
                </div>
            </label>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- Thresholds --}}
    <x-card title="Thresholds" subtitle="when to escalate">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Late · minutes</label>
                    <input type="number" name="thresholds[late_minutes]"
                           value="{{ $settings['thresholds']['late_minutes'] ?? 15 }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">past this, mark as Late</div>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Crowd warn</label>
                    <input type="number" name="thresholds[crowd_warn]"
                           value="{{ $settings['thresholds']['crowd_warn'] ?? 50 }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">people detected per zone</div>
                </div>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- Retention --}}
    <x-card title="Retention" subtitle="how long camera footage and events are kept">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Days</label>
                <input type="number" name="retention[days]"
                       value="{{ $settings['retention']['days'] ?? 30 }}"
                       class="w-32 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- SenseStudio --}}
    <x-card title="SenseStudio" subtitle="face recognition backend">
        <form method="POST" action="{{ route('school.settings.sensestudio.test') }}" class="space-y-2 text-sm">
            @csrf
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Base URL</label>
                <input name="base_url" placeholder="https://sensestudio.example.com"
                       value="{{ $settings['sensestudio']['base_url'] ?? '' }}"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono text-xs focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Username</label>
                    <input name="username"
                           value="{{ $settings['sensestudio']['username'] ?? '' }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Password</label>
                    <input type="password" name="password"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
            </div>
            <button class="inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                Test &amp; save
            </button>
        </form>
    </x-card>
</div>

@endsection
