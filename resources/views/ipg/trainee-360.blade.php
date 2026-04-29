@extends('layouts.shell')
@section('title', $trainee?->name ?? 'Trainee 360')
@section('subtitle', $trainee ? 'Trainee 360 · ' . $trainee->trainee_number : 'pick a trainee from the roster')

@php
    $statusPill = [
        'active'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'suspended'   => 'text-rose-700 bg-rose-50 border-rose-200',
        'graduated'   => 'text-sky-700 bg-sky-50 border-sky-200',
        'withdrawn'   => 'text-zinc-600 bg-zinc-50 border-zinc-200',
    ];
@endphp

@section('content')

@if (! $trainee)
    <div class="flex flex-wrap items-end justify-between gap-4 mb-5">
        <div>
            <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Trainee 360</div>
            <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
                Pick a trainee <span class="text-zinc-400 cursor-blink">from the roster.</span>
            </h1>
        </div>
    </div>

    <a href="{{ route('ipg.trainees') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-zinc-700 text-sm hover:border-zinc-300 transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        Open trainees roster
    </a>
@else
    @php
        $initial = strtoupper(mb_substr($trainee->name, 0, 1));
        $cohort  = $trainee->cohort;
    @endphp

    {{-- Hero --}}
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-5 md:p-6 mb-3">
        <div class="flex items-start gap-5">
            <span class="w-16 h-16 rounded-full bg-zinc-900 text-white flex items-center justify-center text-2xl font-semibold tracking-tight shrink-0">{{ $initial }}</span>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold">Trainee profile</div>
                <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-zinc-900 mt-0.5">{{ $trainee->name }}</h1>
                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-xs">
                    <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">Trainee #</span><div class="font-mono tabular-nums text-zinc-900">{{ $trainee->trainee_number }}</div></div>
                    <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">IC</span><div class="font-mono tabular-nums text-zinc-900">{{ $trainee->ic_number ?: '—' }}</div></div>
                    <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">Gender</span><div class="text-zinc-900">{{ $trainee->gender ?: '—' }}</div></div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-zinc-400">Cohort</span>
                        <div class="text-zinc-900">{{ $cohort?->display_name ?? 'unenrolled' }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-zinc-400">Status</span>
                        <div class="mt-0.5">
                            <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$trainee->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">{{ $trainee->status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Trainee 360 panels — empty states until each module is wired --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-3">
        <x-card class="lg:col-span-4" title="Academic record" subtitle="transcripts · per-semester GPA">
            <div class="text-xs text-zinc-500 py-2">Promoted in Phase 4 (Transcripts module).</div>
        </x-card>
        <x-card class="lg:col-span-4" title="Practicum history" subtitle="placements · observations · evaluations">
            <div class="text-xs text-zinc-500 py-2">Promoted in Phase 5 (Practicum module).</div>
        </x-card>
        <x-card class="lg:col-span-4" title="Co-curriculum" subtitle="participation · units earned">
            <div class="text-xs text-zinc-500 py-2">Promoted in Phase 4 (Co-curriculum module).</div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <x-card title="Hostel (Asrama)" subtitle="room · attendance · discipline">
            <div class="text-xs text-zinc-500 py-2">Promoted in Phase 7 (Hostel module).</div>
        </x-card>
        <x-card title="Leaves" subtitle="MC · cuti · history">
            <div class="text-xs text-zinc-500 py-2">No requests in scaffold.</div>
        </x-card>
        <x-card title="Notes" subtitle="pensyarah remarks">
            <div class="text-xs text-zinc-500 py-2">No notes yet.</div>
        </x-card>
    </div>

    <div class="mt-5">
        <a href="{{ route('ipg.trainees') }}" class="inline-flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-900">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to roster
        </a>
    </div>
@endif

@endsection
