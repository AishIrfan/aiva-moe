@extends('layouts.shell')
@section('title', 'Attendance')
@section('subtitle', $school->name . ' · ' . $date->format('D · M j, Y'))

@php
    $byStatus = $rows->getCollection()->groupBy('status')->map->count();
    $present = (int) ($byStatus['present'] ?? 0);
    $late    = (int) ($byStatus['late'] ?? 0);
    $absent  = (int) ($byStatus['absent'] ?? 0);
    $leave   = (int) (($byStatus['leave'] ?? 0) + ($byStatus['mc'] ?? 0));
    $marked  = $present + $late + $absent + $leave;
    $pct     = $marked > 0 ? round(($present / $marked) * 100, 1) : 0;

    $statusPill = [
        'present' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'late'    => 'text-amber-700 bg-amber-50 border-amber-200',
        'absent'  => 'text-rose-700 bg-rose-50 border-rose-200',
        'leave'   => 'text-sky-700 bg-sky-50 border-sky-200',
        'mc'      => 'text-sky-700 bg-sky-50 border-sky-200',
    ];

    $subPages = [
        ['Follow-up',       'school.attendance-follow-up'],
        ['Records',         'school.attendance-records'],
        ['Monthly summary', 'school.attendance-monthly-summary'],
        ['Warning letters', 'school.attendance-warning-letters'],
    ];
@endphp

@section('content')

{{-- Heading + date picker --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $marked }} marked
            <span class="text-zinc-400">on {{ $date->format('M j') }}.</span>
        </h1>
    </div>

    <form class="flex items-center gap-2">
        <div class="flex items-center gap-1">
            <a href="{{ route('school.attendance', ['date' => $date->copy()->subDay()->toDateString()]) }}"
               class="p-1.5 rounded-md border border-zinc-200 bg-white hover:bg-zinc-50 transition" title="Previous day">
                <svg class="w-3.5 h-3.5 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <input type="date" name="date" value="{{ $date->toDateString() }}"
                   class="bg-white border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:border-zinc-300"/>
            <a href="{{ route('school.attendance', ['date' => $date->copy()->addDay()->toDateString()]) }}"
               class="p-1.5 rounded-md border border-zinc-200 bg-white hover:bg-zinc-50 transition" title="Next day">
                <svg class="w-3.5 h-3.5 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
            </a>
        </div>
        <button class="px-3 py-1.5 rounded-md bg-zinc-900 text-white text-sm font-medium hover:bg-zinc-800 transition">Load</button>
    </form>
</div>

{{-- Sub-page nav --}}
<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($subPages as [$label, $route])
        <a href="{{ route($route) }}"
           class="px-2.5 py-1 rounded-md border border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300 transition">{{ $label }}</a>
    @endforeach
</nav>

{{-- KPI strip --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Attendance %</div>
        <div class="font-mono tabular-nums text-2xl font-semibold tracking-tight {{ $pct >= 90 ? 'text-emerald-700' : ($pct >= 75 ? 'text-amber-700' : 'text-rose-700') }}">
            {{ $pct }}<span class="text-base text-zinc-400">%</span>
        </div>
    </div>
    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700/70 mb-1">Present</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-emerald-700">{{ $present }}</div>
    </div>
    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-amber-700/70 mb-1">Late</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-amber-700">{{ $late }}</div>
    </div>
    <div class="rounded-xl border border-sky-100 bg-sky-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-sky-700/70 mb-1">Leave / MC</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-sky-700">{{ $leave }}</div>
    </div>
    <div class="rounded-xl border border-rose-100 bg-rose-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-rose-700/70 mb-1">Absent</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-rose-700">{{ $absent }}</div>
    </div>
</div>

{{-- Roll list --}}
@if ($rows->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($rows as $r)
            @php $initial = strtoupper(mb_substr($r->student?->name ?? '?', 0, 1)); @endphp
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">
                    {{ $initial }}
                </span>

                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $r->student?->name ?? '—' }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                        <span>{{ $r->schoolClass?->name ?? 'unenrolled' }}</span>
                        @if ($r->absentReason?->label || $r->notes)
                            <span class="text-zinc-300">·</span>
                            <span class="normal-case text-zinc-600 truncate">{{ $r->absentReason?->label ?? $r->notes }}</span>
                        @endif
                    </div>
                </div>

                <span class="hidden md:inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$r->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $r->status }}
                </span>

                {{-- Inline override --}}
                <form method="POST" action="{{ route('school.attendance.override') }}" class="flex items-center gap-1 shrink-0">
                    @csrf
                    <input type="hidden" name="snapshot_id" value="{{ $r->id }}"/>
                    <select name="status"
                            class="bg-zinc-50 border border-zinc-200 rounded-md text-xs px-1.5 py-1 focus:outline-none focus:border-zinc-300">
                        @foreach (['present','absent','late','leave','mc'] as $s)
                            <option value="{{ $s }}" @selected($r->status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <input name="notes" placeholder="note"
                           class="hidden lg:block w-28 bg-zinc-50 border border-zinc-200 rounded-md text-xs px-1.5 py-1 focus:outline-none focus:border-zinc-300"/>
                    <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2 py-1 transition">
                        Save
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $rows->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">No attendance rows for {{ $date->format('M j') }}</div>
        <div class="text-xs text-zinc-500 mt-1">Pick another date or generate the day's roster.</div>
    </div>
@endif

@endsection
