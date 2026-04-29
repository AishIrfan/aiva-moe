@extends('layouts.shell')
@section('title', 'Trainees roster')
@section('subtitle', ($campus?->name ?? 'IPG') . ' · ' . $trainees->total() . ' enrolled')

@php
    $statusPill = [
        'active'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'suspended'   => 'text-rose-700 bg-rose-50 border-rose-200',
        'graduated'   => 'text-sky-700 bg-sky-50 border-sky-200',
        'withdrawn'   => 'text-zinc-600 bg-zinc-50 border-zinc-200',
    ];
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Trainees</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ number_format($trainees->total()) }} {{ Str::plural('trainee', $trainees->total()) }}
            <span class="text-zinc-400">on the roster.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">
        showing {{ $trainees->count() }} on this page
    </div>
</div>

{{-- Filter bar --}}
<form class="flex flex-wrap items-center gap-2 mb-5 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="relative flex-1 min-w-[260px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input name="q" value="{{ request('q') }}" placeholder="Search by name, trainee #, or IC…"
               class="w-full bg-zinc-50 border border-zinc-200 rounded-lg pl-9 pr-3 py-1.5 text-sm
                      focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
    </div>
    <select name="cohort_id"
            class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All cohorts</option>
        @foreach ($cohorts as $c)
            <option value="{{ $c->id }}" @selected(request('cohort_id') == $c->id)>{{ $c->display_name }}</option>
        @endforeach
    </select>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Filter
    </button>
    @if (request('q') || request('cohort_id'))
        <a href="{{ route('ipg.trainees') }}" class="text-xs text-zinc-500 hover:text-zinc-900 underline underline-offset-4">clear</a>
    @endif
</form>

{{-- Roster --}}
@if ($trainees->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($trainees as $t)
            @php
                $initial = strtoupper(mb_substr($t->name, 0, 1));
                $cohort  = $t->cohort;
            @endphp
            <a href="{{ route('ipg.trainee-360', ['trainee' => $t->id]) }}"
               class="px-4 py-3 flex items-center gap-3 hover:bg-zinc-50 transition group">

                <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">
                    {{ $initial }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $t->name }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span>{{ $t->trainee_number }}</span>
                        @if ($t->ic_number)
                            <span class="text-zinc-300">·</span>
                            <span>IC {{ $t->ic_number }}</span>
                        @endif
                    </div>
                </div>

                <div class="hidden sm:flex flex-col items-end shrink-0">
                    @if ($cohort)
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-zinc-700 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-0.5">
                            {{ $cohort->major }}
                        </span>
                        <span class="text-[10px] text-zinc-400 mt-0.5 font-mono tabular-nums">{{ $cohort->program?->code }} · {{ $cohort->intake_label }}</span>
                    @else
                        <span class="text-[10px] text-zinc-400">unenrolled</span>
                    @endif
                </div>

                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$t->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $t->status }}
                </span>

                <span class="text-zinc-300 group-hover:text-emerald-600 transition shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </a>
        @endforeach
    </div>
    <div class="mt-5">{{ $trainees->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">
            @if (request('q') || request('cohort_id'))
                No trainees match this filter
            @elseif (! $campus)
                Pick a campus to view trainees
            @else
                No trainees on the roster yet
            @endif
        </div>
        <div class="text-xs text-zinc-500 mt-1">
            @if (request('q') || request('cohort_id'))
                Try a different name or cohort.
            @elseif (! $campus)
                <a href="{{ route('bpg.campuses') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Open campuses directory →</a>
            @else
                Use enrollment to onboard the first intake.
            @endif
        </div>
    </div>
@endif

@endsection
