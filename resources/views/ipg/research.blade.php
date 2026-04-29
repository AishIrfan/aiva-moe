@extends('layouts.shell')
@section('title', 'Research projects')
@section('subtitle', 'Penyelidikan · final-year tracking')

@php
    $statusPill = [
        'proposal'    => 'text-sky-700 bg-sky-50 border-sky-200',
        'in_progress' => 'text-amber-700 bg-amber-50 border-amber-200',
        'submitted'   => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'evaluated'   => 'text-zinc-700 bg-zinc-50 border-zinc-200',
    ];

    $byStatus = $projects->groupBy('status')->map->count();
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics / Research</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $projects->count() }} {{ Str::plural('project', $projects->count()) }}
            <span class="text-zinc-400">in flight.</span>
        </h1>
    </div>
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">Pick a campus to view research projects</div>
    </div>
@else
    {{-- Status strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
        @foreach (['proposal' => 'Proposal', 'in_progress' => 'In progress', 'submitted' => 'Submitted', 'evaluated' => 'Evaluated'] as $key => $label)
            @php
                $tones = ['proposal' => 'sky', 'in_progress' => 'amber', 'submitted' => 'emerald', 'evaluated' => 'zinc'];
                $tone = $tones[$key];
            @endphp
            <div class="rounded-xl border border-{{ $tone }}-100 bg-{{ $tone }}-50/60 p-3">
                <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-{{ $tone }}-700/70 mb-1">{{ $label }}</div>
                <div class="font-mono tabular-nums text-2xl font-semibold text-{{ $tone }}-700">{{ $byStatus[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    @if ($projects->count() > 0)
        <div class="space-y-3">
            @foreach ($projects as $p)
                @php
                    $milestones = collect($p->milestones ?? []);
                    $done = $milestones->filter(fn ($m) => ! empty($m['done_at']))->count();
                    $pct  = $milestones->count() > 0 ? round(($done / $milestones->count()) * 100) : 0;
                    $initial = strtoupper(mb_substr($p->trainee?->name ?? '?', 0, 1));
                @endphp
                <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-5 hover-lift">
                    <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                        <div class="flex items-start gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">{{ $initial }}</span>
                            <div class="min-w-0">
                                <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-0.5">{{ $p->trainee?->cohort?->display_name }}</div>
                                <div class="text-sm font-semibold text-zinc-900">{{ $p->trainee?->name }}</div>
                                <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">{{ $p->trainee?->trainee_number }}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$p->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                            {{ str_replace('_', ' ', $p->status) }}
                        </span>
                    </div>

                    <div class="text-sm font-medium text-zinc-900 mb-2 leading-snug">{{ $p->title }}</div>

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-[11px] text-zinc-500 mb-3 font-mono tabular-nums">
                        @if ($p->supervisor)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Supervisor</span> <span class="text-zinc-900 normal-case">{{ $p->supervisor->name }}</span></span>
                        @endif
                        @if ($p->started_at)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Started</span> {{ $p->started_at->format('M Y') }}</span>
                        @endif
                        @if ($p->submitted_at)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Submitted</span> {{ $p->submitted_at->format('M Y') }}</span>
                        @endif
                        @if ($p->evaluation_score !== null)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Score</span> <span class="text-emerald-700 font-semibold">{{ $p->evaluation_score }}/100</span></span>
                        @endif
                    </div>

                    {{-- Milestones --}}
                    @if ($milestones->count() > 0)
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-1.5">
                                <span class="text-zinc-500">Milestones · {{ $done }}/{{ $milestones->count() }}</span>
                                <span class="font-mono tabular-nums text-zinc-700">{{ $pct }}%</span>
                            </div>
                            <div class="h-1 rounded-full bg-zinc-100 overflow-hidden mb-2">
                                <div class="h-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <ul class="grid grid-cols-2 lg:grid-cols-4 gap-2 mt-2">
                                @foreach ($milestones as $m)
                                    @php $isDone = ! empty($m['done_at']); @endphp
                                    <li class="rounded-md border border-zinc-200 bg-white px-2 py-1.5">
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="w-1 h-1 rounded-full {{ $isDone ? 'bg-emerald-500' : 'bg-zinc-300' }}"></span>
                                            <span class="text-zinc-900 truncate">{{ $m['label'] }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-400 mt-0.5 font-mono tabular-nums">
                                            {{ $isDone ? 'done · ' . \Carbon\Carbon::parse($m['done_at'])->format('M j') : 'due ' . \Carbon\Carbon::parse($m['due_date'])->format('M j') }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="text-sm font-medium text-zinc-900">No research projects yet</div>
            <div class="text-xs text-zinc-500 mt-1">Final-year trainees register their projects here. Run the IPG demo seed for sample data.</div>
        </div>
    @endif
@endif

@endsection
