@extends('layouts.shell')
@section('title', 'BPG · Overview')
@section('subtitle', 'Bahagian Pendidikan Guru — IPG ministry layer')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry (BPG) / Overview</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $campusCount }} {{ Str::plural('campus', $campusCount) }},
            <span class="text-zinc-400 cursor-blink">one ministry view.</span>
        </h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5 font-mono tabular-nums">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-amber-500"></span>
            </span>
            scaffold mode
        </span>
        <span class="text-zinc-300">·</span>
        <span class="font-mono tabular-nums">{{ now()->format('H:i') }} MYT</span>
    </div>
</div>

<div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/>
    </svg>
    <div class="text-xs text-amber-900">
        <div class="font-medium">BPG layer is currently a scaffold</div>
        <div class="text-amber-800/80 mt-0.5">
            Routes and navigation are wired; ministry-level analytics (campus posture, intake totals, practicum coverage) come online in a later phase. Use <a href="{{ route('bpg.campuses') }}" class="underline hover:text-amber-950">Campuses</a> to drill into a specific IPG.
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <x-card class="hover-lift">
        <x-slot:eyebrow>Campuses registered</x-slot:eyebrow>
        <div class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-900">{{ $campusCount }}</div>
        <a href="{{ route('bpg.campuses') }}" class="inline-flex items-center gap-1 text-[11px] mt-4 text-emerald-700 hover:text-emerald-800 font-medium">
            Open directory
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </x-card>

    <x-card class="hover-lift">
        <x-slot:eyebrow>Trainees · network</x-slot:eyebrow>
        <div class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-300">—</div>
        <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">awaiting aggregate query</div>
    </x-card>

    <x-card class="hover-lift">
        <x-slot:eyebrow>Active practicum windows</x-slot:eyebrow>
        <div class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-300">—</div>
        <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">comes with placement model</div>
    </x-card>
</div>

@endsection
