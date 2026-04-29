@extends('layouts.shell')
@section('title', 'Observations')
@section('subtitle', 'classroom visits · rubric scoring')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / Observations</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $observations->count() }} {{ Str::plural('visit', $observations->count()) }}
            <span class="text-zinc-400">recorded.</span>
        </h1>
    </div>
</div>

@if ($observations->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($observations as $o)
            @php
                $score = $o->rubric_score;
                $tone  = $score === null ? 'zinc' : ($score >= 80 ? 'emerald' : ($score >= 60 ? 'amber' : 'rose'));
            @endphp
            <div class="px-4 py-3 flex items-start gap-3">
                <div class="w-12 h-12 rounded-md bg-{{ $tone }}-50 border border-{{ $tone }}-200 flex flex-col items-center justify-center shrink-0">
                    <div class="font-mono tabular-nums text-sm font-semibold text-{{ $tone }}-700 leading-none">{{ $score ?? '—' }}</div>
                    <div class="text-[9px] text-{{ $tone }}-600/70 mt-0.5">/100</div>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-zinc-900">{{ $o->placement?->trainee?->name }}</span>
                        <span class="text-[10px] font-mono tabular-nums text-zinc-400">{{ $o->observed_at->format('D · M j, Y') }}</span>
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 truncate">{{ $o->lesson_topic }}</div>
                    @if ($o->notes)
                        <div class="text-xs text-zinc-700 mt-1.5 leading-relaxed">{{ $o->notes }}</div>
                    @endif
                    @if ($o->evaluator)
                        <div class="text-[10px] text-zinc-400 mt-1.5 font-mono tabular-nums">observed by {{ $o->evaluator->name }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No observations on file</div>
        <div class="text-xs text-zinc-500 mt-1">Pensyarah Penyelia log classroom visits here once placements are active.</div>
    </div>
@endif

@endsection
