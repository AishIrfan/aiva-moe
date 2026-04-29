@extends('layouts.shell')
@section('title', $school->name)
@section('subtitle', ($school->state ?? '—') . ' · today ' . now()->format('D · M j'))

@php
    $camOnline = $cameras->where('online', true)->count();
    $camTotal  = $cameras->count();
    $camPct    = $camTotal > 0 ? round(($camOnline / $camTotal) * 100, 1) : 0;

    $present  = (int) ($attendance['present'] ?? 0);
    $absent   = (int) (($attendance['absent'] ?? 0) + ($attendance['tidak_hadir'] ?? 0));
    $late     = (int) (($attendance['late'] ?? 0) + ($attendance['lewat'] ?? 0));
    $excused  = (int) (($attendance['mc'] ?? 0) + ($attendance['cuti'] ?? 0) + ($attendance['excused'] ?? 0));
    $marked   = $present + $absent + $late + $excused;
    $attPct   = $marked > 0 ? round(($present / $marked) * 100, 1) : 0;

    $sevCounts = $openEvents->groupBy('severity')->map->count();
    $crit = (int) ($sevCounts['critical'] ?? 0);
    $high = (int) ($sevCounts['high'] ?? 0);
    $low  = max(0, $openEvents->count() - $crit - $high);

    $maxZone = max(1, $hotZones->max('n') ?? 1);

    $sevDot = [
        'critical' => 'bg-rose-500',
        'high'     => 'bg-amber-500',
        'medium'   => 'bg-amber-400',
        'low'      => 'bg-emerald-500',
    ];
    $sevPill = [
        'critical' => 'text-rose-700 bg-rose-50 border-rose-200',
        'high'     => 'text-amber-700 bg-amber-50 border-amber-200',
        'medium'   => 'text-amber-700 bg-amber-50 border-amber-200',
        'low'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
    ];
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Overview</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Today at <span class="text-zinc-400 cursor-blink">{{ $school->name }}.</span>
        </h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5 font-mono tabular-nums">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            </span>
            {{ now()->format('H:i') }} MYT
        </span>
    </div>
</div>

{{-- KPI bento --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 stagger-in">

    {{-- Students --}}
    <x-card class="hover-lift">
        <x-slot:eyebrow>Students enrolled</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight">{{ number_format($studentCount) }}</span>
            <span class="text-xs text-zinc-500">active</span>
        </div>
        <a href="{{ route('school.students') }}" class="inline-flex items-center gap-1 text-[11px] mt-4 text-emerald-700 hover:text-emerald-800 font-medium">
            Open roster
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
    </x-card>

    {{-- Cameras --}}
    <x-card class="hover-lift {{ $camTotal - $camOnline > 0 ? 'strobe-rose' : '' }}">
        <x-slot:eyebrow>Cameras live</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight">{{ $camOnline }}</span>
            <span class="text-zinc-400 text-base font-mono tabular-nums">/ {{ $camTotal }}</span>
        </div>
        <div class="mt-4">
            <div class="flex items-center justify-between text-[11px] mb-1.5">
                <span class="text-zinc-500">{{ $camPct }}% online</span>
                @if ($camTotal - $camOnline > 0)
                    <span class="text-rose-600 font-medium">{{ $camTotal - $camOnline }} offline</span>
                @endif
            </div>
            <div class="shimmer-bar h-1.5">
                <div class="absolute inset-y-0 left-0 rounded-full {{ $camPct >= 95 ? 'bg-emerald-500' : ($camPct >= 80 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $camPct }}%"></div>
            </div>
        </div>
    </x-card>

    {{-- Attendance --}}
    <x-card class="hover-lift">
        <x-slot:eyebrow>Attendance · today</x-slot:eyebrow>
        @if ($marked > 0)
            <div class="flex items-baseline gap-2">
                <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight {{ $attPct >= 90 ? 'text-emerald-700' : ($attPct >= 75 ? 'text-amber-700' : 'text-rose-700') }}">{{ $attPct }}<span class="text-xl text-zinc-400 ml-0.5">%</span></span>
                <span class="text-xs text-zinc-500">{{ $marked }} marked</span>
            </div>
            <div class="mt-4 grid grid-cols-4 gap-1.5 text-center">
                <div class="rounded-md bg-emerald-50 border border-emerald-100 px-1 py-1.5">
                    <div class="font-mono tabular-nums text-sm font-semibold text-emerald-700">{{ $present }}</div>
                    <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">present</div>
                </div>
                <div class="rounded-md bg-amber-50 border border-amber-100 px-1 py-1.5">
                    <div class="font-mono tabular-nums text-sm font-semibold text-amber-700">{{ $late }}</div>
                    <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">late</div>
                </div>
                <div class="rounded-md bg-sky-50 border border-sky-100 px-1 py-1.5">
                    <div class="font-mono tabular-nums text-sm font-semibold text-sky-700">{{ $excused }}</div>
                    <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">mc</div>
                </div>
                <div class="rounded-md bg-rose-50 border border-rose-100 px-1 py-1.5">
                    <div class="font-mono tabular-nums text-sm font-semibold text-rose-700">{{ $absent }}</div>
                    <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">absent</div>
                </div>
            </div>
        @else
            <div class="font-mono tabular-nums text-4xl font-semibold text-zinc-300">—</div>
            <div class="text-xs text-zinc-500 mt-2">Attendance not marked yet today.</div>
        @endif
    </x-card>

    {{-- Open events --}}
    <x-card class="hover-lift {{ $crit > 0 ? 'strobe-rose' : '' }}">
        <x-slot:eyebrow>Open events</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight">{{ $openEvents->count() }}</span>
            @if ($crit > 0)
                <span class="text-xs text-rose-600 font-medium">{{ $crit }} critical</span>
            @endif
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-md bg-rose-50 border border-rose-100 px-1 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-rose-700">{{ $crit }}</div>
                <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">critical</div>
            </div>
            <div class="rounded-md bg-amber-50 border border-amber-100 px-1 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-amber-700">{{ $high }}</div>
                <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">high</div>
            </div>
            <div class="rounded-md bg-zinc-50 border border-zinc-100 px-1 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-zinc-700">{{ $low }}</div>
                <div class="text-[9px] uppercase tracking-wider text-zinc-500 mt-0.5">other</div>
            </div>
        </div>
    </x-card>
</div>

{{-- Bottom split: events feed / hot zones --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mt-3">

    <x-card class="lg:col-span-7" title="Open events" subtitle="latest 10 · still open">
        <x-slot:action>
            <a href="{{ route('school.alerts') }}" class="text-emerald-700 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
                All alerts
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </x-slot:action>

        @if ($openEvents->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($openEvents as $e)
                    <li class="px-1 py-2.5 flex items-start gap-3">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-2 flex-wrap">
                                <span class="text-sm font-medium text-zinc-900 leading-tight">{{ $e->title }}</span>
                                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $sevPill[$e->severity] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                                    {{ $e->severity }}
                                </span>
                            </div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                                <span class="uppercase tracking-wide">{{ $e->type }}</span>
                                <span class="text-zinc-300">·</span>
                                <span>{{ $e->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="py-8 text-center">
                <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-50 border border-emerald-100 mb-2">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </div>
                <div class="text-sm font-medium text-zinc-900">All clear</div>
                <div class="text-xs text-zinc-500 mt-1">No events open right now.</div>
            </div>
        @endif
    </x-card>

    <x-card class="lg:col-span-5" title="Hot zones" subtitle="last 7 days · top 5">
        @if ($hotZones->count() > 0)
            <ul class="space-y-3 mt-1">
                @foreach ($hotZones as $z)
                    <li>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-medium text-zinc-700 truncate">{{ $z->zone?->name ?? 'Unknown zone' }}</span>
                            <span class="font-mono tabular-nums text-zinc-500">{{ $z->n }}</span>
                        </div>
                        <div class="h-1 rounded-full bg-zinc-100 overflow-hidden">
                            <div class="h-full {{ $z->n > $maxZone * 0.66 ? 'bg-rose-500' : ($z->n > $maxZone * 0.33 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                 style="width: {{ ($z->n / $maxZone) * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="py-6 text-center">
                <div class="text-sm text-zinc-500">No hot zones in the last 7 days.</div>
            </div>
        @endif
    </x-card>
</div>

{{-- Live signals marquee --}}
@php
    $signals = [
        ['STUDENTS',        number_format($studentCount),                                  'emerald'],
        ['CAMERAS',         $camOnline . '/' . $camTotal . ' online',                       $camPct >= 95 ? 'emerald' : ($camPct >= 80 ? 'amber' : 'rose')],
        ['UPTIME',          $camPct . '%',                                                  $camPct >= 95 ? 'emerald' : 'amber'],
        ['ATTENDANCE',      $marked > 0 ? $attPct . '%' : 'pending',                        $marked > 0 && $attPct >= 90 ? 'emerald' : ($marked > 0 && $attPct >= 75 ? 'amber' : 'rose')],
        ['MARKED',          $marked,                                                        'zinc'],
        ['OPEN EVENTS',     $openEvents->count(),                                           $crit > 0 ? 'rose' : ($high > 0 ? 'amber' : 'emerald')],
        ['CRITICAL',        $crit,                                                          $crit > 0 ? 'rose' : 'emerald'],
        ['HOT ZONES · 7D',  $hotZones->count(),                                             $hotZones->count() > 3 ? 'amber' : 'zinc'],
        ['LAST SYNC',       now()->format('H:i:s') . ' MYT',                                'emerald'],
    ];
    $tone = [
        'emerald' => ['text-emerald-700', 'bg-emerald-500'],
        'amber'   => ['text-amber-700',   'bg-amber-500'],
        'rose'    => ['text-rose-700',    'bg-rose-500'],
        'zinc'    => ['text-zinc-700',    'bg-zinc-400'],
    ];
@endphp

<div class="mt-3 bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
    <div class="flex items-center gap-3 px-4 py-2 border-b border-zinc-100 bg-zinc-50/40">
        <span class="relative flex h-1.5 w-1.5">
            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        </span>
        <span class="text-[10px] uppercase tracking-[0.18em] font-semibold text-zinc-500">Live signals</span>
        <span class="text-[10px] text-zinc-400 font-mono tabular-nums ml-auto">drift detection on</span>
    </div>

    <div class="marquee py-3">
        <div class="marquee-track">
            @for ($pass = 0; $pass < 2; $pass++)
                <div class="flex shrink-0 items-center gap-x-10 px-6">
                    @foreach ($signals as $sig)
                        @php [$label, $value, $col] = $sig; [$txt, $bg] = $tone[$col]; @endphp
                        <div class="flex items-center gap-2.5 whitespace-nowrap">
                            <span class="w-1 h-1 rounded-full {{ $bg }}"></span>
                            <span class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold">{{ $label }}</span>
                            <span class="font-mono tabular-nums text-sm font-semibold {{ $txt }}">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>
</div>

@endsection
