@extends('layouts.shell')
@section('title', 'Transcripts')
@section('subtitle', $trainee?->name ?? 'pick a trainee')

@php
    $semesterTotals = collect();
    if ($trainee && $entries->isNotEmpty()) {
        $semesterTotals = $entries->map(function ($semEntries, $semId) {
            $totalCredits = $semEntries->sum(fn ($e) => $e->course?->credit_hours ?? 0);
            $weightedSum  = $semEntries->sum(fn ($e) => ($e->course?->credit_hours ?? 0) * (float) $e->grade_point);
            return (object) [
                'semester'    => $semEntries->first()->semester,
                'totalCredits'=> $totalCredits,
                'gpa'         => $totalCredits > 0 ? round($weightedSum / $totalCredits, 2) : 0,
                'entries'     => $semEntries,
            ];
        });
    }

    $cgpaTotalCredits = $semesterTotals->sum('totalCredits');
    $cgpaWeighted     = $entries->flatten()->sum(fn ($e) => ($e->course?->credit_hours ?? 0) * (float) $e->grade_point);
    $cgpa = $cgpaTotalCredits > 0 ? round($cgpaWeighted / $cgpaTotalCredits, 2) : 0;
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics / Transcripts</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            @if ($trainee)
                CGPA <span class="font-mono tabular-nums text-emerald-700">{{ number_format($cgpa, 2) }}</span>
                <span class="text-zinc-400">· {{ $trainee->name }}.</span>
            @else
                No transcripts <span class="text-zinc-400 cursor-blink">yet.</span>
            @endif
        </h1>
    </div>

    @if ($trainee)
        <form method="GET" class="flex items-center gap-2">
            <select name="trainee" onchange="this.form.submit()" class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-sm focus:outline-none focus:border-zinc-300">
                @foreach ($trainees as $t)
                    <option value="{{ $t->id }}" @selected($t->id === $trainee->id)>{{ $t->name }} · {{ $t->trainee_number }}</option>
                @endforeach
            </select>
        </form>
    @endif
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">Pick a campus to view transcripts</div>
    </div>
@elseif (! $trainee)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No transcript entries yet</div>
        <div class="text-xs text-zinc-500 mt-1">Run the IPG demo seed or wait for the academics pipeline to start writing entries.</div>
    </div>
@else
    {{-- KPI strip --}}
    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700/70 mb-1">CGPA</div>
            <div class="font-mono tabular-nums text-3xl font-semibold text-emerald-700">{{ number_format($cgpa, 2) }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Total credits</div>
            <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight">{{ $cgpaTotalCredits }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Semesters on record</div>
            <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight">{{ $semesterTotals->count() }}</div>
        </div>
    </div>

    {{-- Per-semester breakdown --}}
    @foreach ($semesterTotals as $st)
        <x-card class="mb-3" :title="$st->semester?->name" :subtitle="'GPA ' . number_format($st->gpa, 2) . ' · ' . $st->totalCredits . ' credits'">
            <x-slot:action>
                <span class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">{{ $st->semester?->code }}</span>
            </x-slot:action>

            <div class="-mx-5 -mb-5 border-t border-zinc-100">
                <div class="grid grid-cols-12 gap-3 px-5 py-2 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">
                    <div class="col-span-2">Code</div>
                    <div class="col-span-7">Course</div>
                    <div class="col-span-1 text-right">Credits</div>
                    <div class="col-span-1 text-right">Grade</div>
                    <div class="col-span-1 text-right">Point</div>
                </div>
                @foreach ($st->entries as $e)
                    <div class="grid grid-cols-12 gap-3 px-5 py-2 border-b border-zinc-50 last:border-b-0 items-center text-sm">
                        <div class="col-span-2 font-mono text-xs text-zinc-700">{{ $e->course?->code }}</div>
                        <div class="col-span-7 text-zinc-900 truncate">{{ $e->course?->title }}</div>
                        <div class="col-span-1 text-right font-mono tabular-nums text-zinc-700">{{ $e->course?->credit_hours }}</div>
                        <div class="col-span-1 text-right font-mono tabular-nums font-semibold {{ str_starts_with($e->grade_letter, 'A') ? 'text-emerald-700' : (str_starts_with($e->grade_letter, 'B') ? 'text-zinc-900' : 'text-amber-700') }}">{{ $e->grade_letter }}</div>
                        <div class="col-span-1 text-right font-mono tabular-nums text-zinc-700">{{ number_format((float) $e->grade_point, 2) }}</div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endforeach
@endif

@endsection
