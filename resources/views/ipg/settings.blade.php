@extends('layouts.shell')
@section('title', 'IPG settings')
@section('subtitle', 'thresholds · notifications · integrations')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Settings</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Tune <span class="text-zinc-400">how the campus behaves.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

    <x-card title="Notifications" subtitle="how trainees & pensyarah are reached">
        <form method="POST" action="{{ route('ipg.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[email]" value="1" class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">Email</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Send announcements and digests via SMTP.</div>
                </div>
            </label>
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[sms]" value="1" class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">SMS</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Critical alerts only — uses your provider quota.</div>
                </div>
            </label>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    <x-card title="Thresholds" subtitle="when to escalate">
        <form method="POST" action="{{ route('ipg.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Late · minutes</label>
                    <input type="number" name="thresholds[late_minutes]" value="15"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Warning · absences/30d</label>
                    <input type="number" name="thresholds[warning_absences]" value="5"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    <x-card title="Practicum defaults" subtitle="placement window length, observation cadence">
        <form method="POST" action="{{ route('ipg.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Placement · weeks</label>
                    <input type="number" name="practicum[weeks]" value="12"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Observations · per trainee</label>
                    <input type="number" name="practicum[observations]" value="3"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    <x-card title="Retention" subtitle="how long campus records are kept">
        <form method="POST" action="{{ route('ipg.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Days</label>
                <input type="number" name="retention[days]" value="365"
                       class="w-32 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>
</div>

@endsection
