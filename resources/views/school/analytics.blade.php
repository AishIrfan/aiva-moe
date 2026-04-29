@extends('layouts.shell')
@section('title', 'Analytics')
@section('subtitle', 'last 30 days · events · attendance trend')

@php
    $totalByType = (int) collect($byType)->sum();
    $totalBySev  = (int) collect($bySeverity)->sum();
    $maxByType   = max(1, collect($byType)->max() ?? 1);

    $sevColor = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
        'low'      => 'bg-emerald-500',
    ];

    // Build attendance bars
    $attRows = [];
    foreach ($attendanceTrend as $date => $rows) {
        $rm = $rows->keyBy('status');
        $present = (int) ($rm['present']->n ?? 0);
        $absent  = (int) ($rm['absent']->n  ?? 0);
        $late    = (int) ($rm['late']->n    ?? 0);
        $leave   = (int) ($rm['leave']->n   ?? 0);
        $mc      = (int) ($rm['mc']->n      ?? 0);
        $total   = $present + $absent + $late + $leave + $mc;
        $attRows[] = compact('date', 'present', 'absent', 'late', 'leave', 'mc', 'total');
    }
    $maxDay = max(1, collect($attRows)->max('total') ?? 1);
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Analytics</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ number_format($totalByType) }} events
            <span class="text-zinc-400">in the last 30 days.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
    <x-card title="Events by type" subtitle="30 day window">
        @if (count($byType) > 0)
            <ul class="space-y-2.5 mt-1">
                @foreach ($byType as $type => $n)
                    <li>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-medium uppercase tracking-wide text-zinc-700">{{ $type }}</span>
                            <span class="font-mono tabular-nums text-zinc-500">{{ number_format($n) }}</span>
                        </div>
                        <div class="h-1 rounded-full bg-zinc-100 overflow-hidden">
                            <div class="h-full bg-zinc-900" style="width: {{ ($n / $maxByType) * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No events in this window.</div>
        @endif
    </x-card>

    <x-card title="Events by severity" subtitle="30 day window">
        @if (count($bySeverity) > 0)
            <ul class="space-y-2.5 mt-1">
                @foreach ($bySeverity as $sev => $n)
                    @php $maxSev = max(1, collect($bySeverity)->max() ?? 1); @endphp
                    <li>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-sm {{ $sevColor[$sev] ?? 'bg-zinc-400' }}"></span>
                                <span class="font-medium uppercase tracking-wide text-zinc-700">{{ $sev }}</span>
                            </span>
                            <span class="font-mono tabular-nums text-zinc-500">{{ number_format($n) }}</span>
                        </div>
                        <div class="h-1 rounded-full bg-zinc-100 overflow-hidden">
                            <div class="h-full {{ $sevColor[$sev] ?? 'bg-zinc-400' }}" style="width: {{ ($n / $maxSev) * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No events in this window.</div>
        @endif
    </x-card>
</div>

<x-card title="Attendance trend" subtitle="last 14 days · stacked by status">
    @if (count($attRows) > 0)
        <div class="flex items-end gap-[3px] h-40 mt-3 mb-2">
            @foreach ($attRows as $r)
                @php $heightPct = ($r['total'] / $maxDay) * 100; @endphp
                <div class="group flex-1 flex flex-col-reverse h-full relative" title="{{ $r['date'] }}">
                    @if ($r['total'] > 0)
                        @if ($r['present']) <div class="bg-emerald-500" style="height: {{ ($r['present'] / $r['total']) * $heightPct }}%"></div> @endif
                        @if ($r['late'])    <div class="bg-amber-500"   style="height: {{ ($r['late'] / $r['total']) * $heightPct }}%"></div>    @endif
                        @if ($r['leave'])   <div class="bg-sky-500"     style="height: {{ ($r['leave'] / $r['total']) * $heightPct }}%"></div>   @endif
                        @if ($r['mc'])      <div class="bg-sky-400"     style="height: {{ ($r['mc'] / $r['total']) * $heightPct }}%"></div>      @endif
                        @if ($r['absent'])  <div class="bg-rose-500"    style="height: {{ ($r['absent'] / $r['total']) * $heightPct }}%"></div>  @endif
                    @endif
                    <div class="absolute -top-1 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                        <div class="bg-zinc-900 text-white text-[10px] font-mono tabular-nums rounded px-1.5 py-1 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($r['date'])->format('M j') }} · {{ $r['total'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between text-[10px] font-mono tabular-nums text-zinc-400 mt-1 mb-3">
            <span>{{ \Carbon\Carbon::parse($attRows[0]['date'])->format('M j') }}</span>
            <span>{{ \Carbon\Carbon::parse(end($attRows)['date'])->format('M j') }}</span>
        </div>

        {{-- Legend --}}
        <ul class="flex flex-wrap gap-x-4 gap-y-1 text-xs pt-2 border-t border-zinc-100">
            <li class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-emerald-500"></span><span class="text-zinc-600">Present</span></li>
            <li class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-amber-500"></span><span class="text-zinc-600">Late</span></li>
            <li class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-sky-500"></span><span class="text-zinc-600">Leave</span></li>
            <li class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-sky-400"></span><span class="text-zinc-600">MC</span></li>
            <li class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-rose-500"></span><span class="text-zinc-600">Absent</span></li>
        </ul>
    @else
        <div class="text-xs text-zinc-500 py-4 text-center">No attendance data in window.</div>
    @endif
</x-card>

@endsection
