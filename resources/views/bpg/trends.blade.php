@extends('layouts.shell')
@section('title', 'BPG · Trends')
@section('subtitle', 'network-wide IPG activity')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry (BPG) / Trends</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Network trends <span class="text-zinc-400 cursor-blink">scaffold.</span>
        </h1>
    </div>
</div>

<div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/>
    </svg>
    <div class="text-xs text-amber-900">
        <div class="font-medium">Trends not yet aggregated</div>
        <div class="text-amber-800/80 mt-0.5">
            Cross-campus activity, intake volume, practicum coverage, and discipline trends will be aggregated here once the underlying modules are wired and have data flowing.
        </div>
    </div>
</div>

<x-card title="What this page will show" subtitle="when the modules are live">
    <ul class="space-y-2.5 text-sm">
        <li class="flex items-start gap-2.5">
            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-zinc-700">Daily event volume across all IPGs, stacked by category.</span>
        </li>
        <li class="flex items-start gap-2.5">
            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-zinc-700">Practicum placement coverage by host school region.</span>
        </li>
        <li class="flex items-start gap-2.5">
            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-zinc-700">Trainee retention and graduation rate per cohort intake.</span>
        </li>
        <li class="flex items-start gap-2.5">
            <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span class="text-zinc-700">Per-campus leaderboards on attendance, discipline, and incident volume.</span>
        </li>
    </ul>
</x-card>

@endsection
