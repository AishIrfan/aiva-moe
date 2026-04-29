@extends('layouts.shell')
@section('title', 'Monthly summary')
@section('subtitle', $month)

@php
    $subPages = [
        ['Today',           'school.attendance'],
        ['Follow-up',       'school.attendance-follow-up'],
        ['Records',         'school.attendance-records'],
        ['Monthly summary', 'school.attendance-monthly-summary', true],
        ['Warning letters', 'school.attendance-warning-letters'],
    ];

    $totalStudents = is_iterable($rows) ? count($rows) : 0;
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $totalStudents }} students,
            <span class="text-zinc-400">{{ $month }}.</span>
        </h1>
    </div>
</div>

<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($subPages as $sp)
        <a href="{{ route($sp[1]) }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ ($sp[2] ?? false) ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">{{ $sp[0] }}</a>
    @endforeach
</nav>

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="flex items-center gap-2 text-xs">
        <label class="text-zinc-500">Month</label>
        <input type="month" name="month" value="{{ $month }}"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Apply
    </button>
</form>

@if ($totalStudents > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
        {{-- Table header --}}
        <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-100 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">
            <div class="col-span-5">Student</div>
            <div class="col-span-1 text-right">Present</div>
            <div class="col-span-1 text-right">Late</div>
            <div class="col-span-1 text-right">Absent</div>
            <div class="col-span-1 text-right">Leave</div>
            <div class="col-span-1 text-right">MC</div>
            <div class="col-span-2 text-right">Mix</div>
        </div>

        @foreach ($rows as $sid => $byStatus)
            @php
                $sm = $byStatus->keyBy('status');
                $present = (int) ($sm['present']->n ?? 0);
                $absent  = (int) ($sm['absent']->n  ?? 0);
                $late    = (int) ($sm['late']->n    ?? 0);
                $leave   = (int) ($sm['leave']->n   ?? 0);
                $mc      = (int) ($sm['mc']->n      ?? 0);
                $total   = max(1, $present + $absent + $late + $leave + $mc);
            @endphp
            <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-50 last:border-b-0 items-center">
                <div class="col-span-5 text-sm font-medium text-zinc-900 truncate">{{ $students[$sid]->name ?? '—' }}</div>
                <div class="col-span-1 text-right text-sm font-mono tabular-nums {{ $present > 0 ? 'text-emerald-700' : 'text-zinc-400' }}">{{ $present }}</div>
                <div class="col-span-1 text-right text-sm font-mono tabular-nums {{ $late > 0 ? 'text-amber-700' : 'text-zinc-400' }}">{{ $late }}</div>
                <div class="col-span-1 text-right text-sm font-mono tabular-nums {{ $absent > 0 ? 'text-rose-700' : 'text-zinc-400' }}">{{ $absent }}</div>
                <div class="col-span-1 text-right text-sm font-mono tabular-nums {{ $leave > 0 ? 'text-sky-700' : 'text-zinc-400' }}">{{ $leave }}</div>
                <div class="col-span-1 text-right text-sm font-mono tabular-nums {{ $mc > 0 ? 'text-sky-700' : 'text-zinc-400' }}">{{ $mc }}</div>
                <div class="col-span-2">
                    <div class="flex h-1.5 rounded-full overflow-hidden bg-zinc-100">
                        @if ($present) <div class="bg-emerald-500" style="width: {{ ($present / $total) * 100 }}%"></div> @endif
                        @if ($late)    <div class="bg-amber-500"   style="width: {{ ($late / $total) * 100 }}%"></div>    @endif
                        @if ($leave)   <div class="bg-sky-500"     style="width: {{ ($leave / $total) * 100 }}%"></div>   @endif
                        @if ($mc)      <div class="bg-sky-400"     style="width: {{ ($mc / $total) * 100 }}%"></div>      @endif
                        @if ($absent)  <div class="bg-rose-500"    style="width: {{ ($absent / $total) * 100 }}%"></div>  @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No data for {{ $month }}</div>
        <div class="text-xs text-zinc-500 mt-1">Pick a different month above.</div>
    </div>
@endif

@endsection
