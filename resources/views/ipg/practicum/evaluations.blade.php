@extends('layouts.shell')
@section('title', 'Evaluations')
@section('subtitle', 'final practicum grading · feeds transcripts')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / Evaluations</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $evaluations->count() }} {{ Str::plural('evaluation', $evaluations->count()) }}
            <span class="text-zinc-400">recorded.</span>
        </h1>
    </div>
</div>

@if ($evaluations->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach ($evaluations as $e)
            @php
                $score = $e->score;
                $tone  = $score === null ? 'zinc' : ($score >= 80 ? 'emerald' : ($score >= 60 ? 'amber' : 'rose'));
                $initial = strtoupper(mb_substr($e->placement?->trainee?->name ?? '?', 0, 1));
            @endphp
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-4 hover-lift">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">{{ $initial }}</span>
                        <div class="min-w-0">
                            <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-0.5">{{ $e->placement?->trainee?->cohort?->display_name }}</div>
                            <div class="text-sm font-semibold text-zinc-900">{{ $e->placement?->trainee?->name }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">{{ $e->placement?->trainee?->trainee_number }}</div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="font-mono tabular-nums text-2xl font-semibold text-{{ $tone }}-700 leading-none">{{ $e->grade_letter ?? '—' }}</div>
                        <div class="text-[10px] text-zinc-400 mt-1 font-mono tabular-nums">{{ $score }}/100</div>
                    </div>
                </div>

                @if ($e->comments)
                    <div class="text-xs text-zinc-700 leading-relaxed mb-3">{{ $e->comments }}</div>
                @endif

                <div class="flex items-center gap-3 text-[10px] text-zinc-400 font-mono tabular-nums">
                    @if ($e->evaluator)
                        <span>by {{ $e->evaluator->name }}</span>
                    @endif
                    @if ($e->evaluated_at)
                        <span class="text-zinc-300">·</span>
                        <span>{{ $e->evaluated_at->format('D · M j, Y') }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No evaluations on file</div>
        <div class="text-xs text-zinc-500 mt-1">Final practicum grades land here once placements complete.</div>
    </div>
@endif

@endsection
