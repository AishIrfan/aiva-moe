@extends('layouts.shell')
@section('title', 'Placements')
@section('subtitle', 'Praktikum · trainee → host school')

@php
    $statusPill = [
        'scheduled' => 'text-sky-700 bg-sky-50 border-sky-200',
        'active'    => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'completed' => 'text-zinc-700 bg-zinc-50 border-zinc-200',
        'cancelled' => 'text-rose-700 bg-rose-50 border-rose-200',
    ];
    $byStatus = $placements->groupBy('status')->map->count();
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / Placements</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $placements->count() }} {{ Str::plural('placement', $placements->count()) }}
            <span class="text-zinc-400">on the wire.</span>
        </h1>
    </div>
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">Pick a campus</div>
    </div>
@else
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
        @foreach (['active' => 'Active', 'scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
            @php $tone = ['active' => 'emerald', 'scheduled' => 'sky', 'completed' => 'zinc', 'cancelled' => 'rose'][$key]; @endphp
            <div class="rounded-xl border border-{{ $tone }}-100 bg-{{ $tone }}-50/60 p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-{{ $tone }}-700/70 mb-1">{{ $label }}</div>
                <div class="font-mono tabular-nums text-2xl font-semibold text-{{ $tone }}-700">{{ $byStatus[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    @if ($placements->count() > 0)
        <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
            @foreach ($placements as $p)
                @php $initial = strtoupper(mb_substr($p->trainee?->name ?? '?', 0, 1)); @endphp
                <div class="px-4 py-3 flex items-start gap-3 flex-wrap md:flex-nowrap">
                    <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">{{ $initial }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-zinc-900">{{ $p->trainee?->name }}</span>
                            <span class="text-[10px] font-mono tabular-nums text-zinc-400">{{ $p->trainee?->trainee_number }}</span>
                            <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$p->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                                {{ $p->status }}
                            </span>
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 font-mono tabular-nums">
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Host</span> <span class="text-zinc-900 normal-case">{{ $p->hostSchool?->name }}</span></span>
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Window</span> {{ $p->start_date->format('M j') }} → {{ $p->end_date->format('M j, Y') }}</span>
                            @if ($p->supervisor)
                                <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Supervisor</span> <span class="text-zinc-900 normal-case">{{ $p->supervisor->name }}</span></span>
                            @endif
                        </div>
                        @if (! empty($p->subjects) || ! empty($p->levels))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach (($p->subjects ?? []) as $subject)
                                    <span class="text-[10px] font-medium text-zinc-700 bg-zinc-50 border border-zinc-200 rounded px-1.5 py-0.5">{{ $subject }}</span>
                                @endforeach
                                @foreach (($p->levels ?? []) as $level)
                                    <span class="text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">{{ $level }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="text-sm font-medium text-zinc-900">No placements yet</div>
            <div class="text-xs text-zinc-500 mt-1">Penyelaras Praktikum will assign trainees to host schools here.</div>
        </div>
    @endif
@endif

@endsection
