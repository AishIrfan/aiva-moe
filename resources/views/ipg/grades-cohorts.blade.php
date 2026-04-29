@extends('layouts.shell')
@section('title', 'Grades & cohorts')
@section('subtitle', 'PISMP × Major × Intake')

@php
    // Group cohorts by program for display.
    $byProgram = $cohorts->groupBy(fn ($c) => $c->program?->code ?? '—');
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $cohorts->count() }} {{ Str::plural('cohort', $cohorts->count()) }},
            <span class="text-zinc-400">{{ $byProgram->count() }} {{ Str::plural('program', $byProgram->count()) }}.</span>
        </h1>
    </div>
</div>

{{-- Add cohort form --}}
<x-card class="mb-3" title="Add cohort" subtitle="define a new (Program × Major × Intake) tuple">
    <form method="POST" action="{{ route('ipg.cohorts.store') }}" class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
        @csrf
        <select name="program_id" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            @foreach ($programs as $p)
                <option value="{{ $p->id }}" @disabled(! $p->is_active)>{{ $p->code }}{{ $p->is_active ? '' : ' (inactive)' }}</option>
            @endforeach
        </select>
        <input name="major" placeholder="Pengkhususan (e.g. Matematik)" required
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
        <input name="intake_label" placeholder="Ambilan (e.g. Jun 2024)" required
               class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Add cohort</button>
    </form>
</x-card>

@if ($cohorts->count() > 0)
    @foreach ($byProgram as $programCode => $programCohorts)
        <div class="mb-3">
            <div class="flex items-center gap-2 mb-2 px-1">
                <span class="text-[10px] uppercase tracking-[0.14em] text-zinc-500 font-semibold">{{ $programCode }}</span>
                <span class="text-[10px] text-zinc-400 font-mono tabular-nums">{{ $programCohorts->count() }} {{ Str::plural('cohort', $programCohorts->count()) }}</span>
                <div class="flex-1 h-px bg-zinc-200"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach ($programCohorts as $c)
                    <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-4 hover-lift">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="min-w-0">
                                <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-0.5">{{ $c->program?->code }} · {{ $c->intake_label }}</div>
                                <div class="text-sm font-semibold text-zinc-900 leading-tight">{{ $c->major }}</div>
                            </div>
                            <span class="inline-flex items-center text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">
                                {{ $c->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px] mb-3">
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-zinc-400">Trainees</div>
                                <div class="font-mono tabular-nums text-zinc-900 mt-0.5">{{ $traineeCounts[$c->id] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase tracking-wider text-zinc-400">Intake date</div>
                                <div class="font-mono tabular-nums text-zinc-900 mt-0.5">{{ $c->intake_date?->format('M Y') }}</div>
                            </div>
                        </div>

                        <a href="{{ route('ipg.trainees', ['cohort_id' => $c->id]) }}" class="inline-flex items-center gap-1 text-[11px] text-emerald-700 hover:text-emerald-800 font-medium">
                            View roster
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No cohorts defined yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use the form above to add one.</div>
    </div>
@endif

@endsection
