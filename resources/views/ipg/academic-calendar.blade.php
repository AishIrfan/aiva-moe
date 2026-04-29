@extends('layouts.shell')
@section('title', 'Academic calendar')
@section('subtitle', $current?->name ?? 'no current semester')

@php
    // Synthesize a list of standard window markers off the current semester for
    // the visual scaffold. When real data lands, these will come from a campus-
    // scoped `academic_events` table (or similar).
    $markers = [];
    if ($current) {
        $start = $current->start_date;
        $end   = $current->end_date;
        $span  = max(1, $start->diffInDays($end));

        $markers = [
            ['label' => 'Semester begins',      'date' => $start,                                 'tone' => 'emerald'],
            ['label' => 'Course registration',  'date' => $start->copy()->addDays(7),             'tone' => 'sky'],
            ['label' => 'Mid-semester break',   'date' => $start->copy()->addDays((int) ($span * 0.45)), 'tone' => 'zinc'],
            ['label' => 'Practicum window',     'date' => $start->copy()->addDays((int) ($span * 0.55)), 'tone' => 'amber'],
            ['label' => 'Final exam week',      'date' => $end->copy()->subDays(14),              'tone' => 'rose'],
            ['label' => 'Semester ends',        'date' => $end,                                   'tone' => 'emerald'],
        ];
    }

    $toneClasses = [
        'emerald' => ['bg-emerald-500', 'text-emerald-700 bg-emerald-50 border-emerald-200'],
        'amber'   => ['bg-amber-500',   'text-amber-700 bg-amber-50 border-amber-200'],
        'rose'    => ['bg-rose-500',    'text-rose-700 bg-rose-50 border-rose-200'],
        'sky'     => ['bg-sky-500',     'text-sky-700 bg-sky-50 border-sky-200'],
        'zinc'    => ['bg-zinc-400',    'text-zinc-700 bg-zinc-50 border-zinc-200'],
    ];

    $today    = now()->startOfDay();
    $progress = $current ? min(100, max(0, round(($current->start_date->diffInDays($today) / max(1, $current->start_date->diffInDays($current->end_date))) * 100, 1))) : 0;
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Campus System</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            @if ($current)
                {{ $current->code }}
                <span class="text-zinc-400">in flight.</span>
            @else
                No current semester
                <span class="text-zinc-400 cursor-blink">defined.</span>
            @endif
        </h1>
    </div>
</div>

<div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/>
    </svg>
    <div class="text-xs text-amber-900">
        <div class="font-medium">Calendar markers below are synthesized off the current semester</div>
        <div class="text-amber-800/80 mt-0.5">Phase 5 introduces a real <code class="font-mono text-[10px] bg-amber-100/80 rounded px-1">academic_events</code> table for the campus. Practicum windows, exam weeks, and registration periods will then come from there.</div>
    </div>
</div>

@if ($current)
    {{-- Semester progress card --}}
    <x-card class="mb-3" title="{{ $current->name }}" subtitle="{{ $current->start_date->format('M j, Y') }} → {{ $current->end_date->format('M j, Y') }}">
        <x-slot:action>
            <span class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">Current</span>
        </x-slot:action>

        <div class="mt-1">
            <div class="flex items-center justify-between text-[11px] mb-1.5">
                <span class="text-zinc-500">Progress</span>
                <span class="font-mono tabular-nums text-zinc-700">{{ $progress }}%</span>
            </div>
            <div class="shimmer-bar h-1.5">
                <div class="absolute inset-y-0 left-0 bg-emerald-500 rounded-full" style="width: {{ $progress }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[10px] font-mono tabular-nums text-zinc-400 mt-1.5">
                <span>{{ $current->start_date->format('M j') }}</span>
                <span class="text-zinc-700">today · {{ $today->format('M j, Y') }}</span>
                <span>{{ $current->end_date->format('M j') }}</span>
            </div>
        </div>
    </x-card>

    {{-- Markers timeline --}}
    <x-card title="Window markers" subtitle="key dates in this semester">
        <ul class="divide-y divide-zinc-100 -mx-1">
            @foreach ($markers as $m)
                @php
                    [$dotClass, $pillClass] = $toneClasses[$m['tone']] ?? $toneClasses['zinc'];
                    $isPast    = $m['date']->lt($today);
                    $isToday   = $m['date']->isSameDay($today);
                @endphp
                <li class="px-1 py-2.5 flex items-center gap-3">
                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dotClass }} {{ $isToday ? 'animate-pulse' : '' }}"></span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-zinc-900">{{ $m['label'] }}</div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                            {{ $m['date']->format('D · M j, Y') }}
                            @if ($isToday)
                                <span class="text-emerald-700 font-semibold ml-1">· today</span>
                            @elseif ($isPast)
                                <span class="text-zinc-400 ml-1">· {{ $m['date']->diffForHumans() }}</span>
                            @else
                                <span class="text-zinc-500 ml-1">· in {{ $today->diffInDays($m['date']) }}d</span>
                            @endif
                        </div>
                    </div>
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $pillClass }}">
                        {{ $m['tone'] }}
                    </span>
                </li>
            @endforeach
        </ul>
    </x-card>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No current semester</div>
        <div class="text-xs text-zinc-500 mt-1">Mark a semester as current via the seeder or admin tooling to populate this view.</div>
    </div>
@endif

@endsection
