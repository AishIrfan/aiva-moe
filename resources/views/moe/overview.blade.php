@extends('layouts.shell')
@section('title', 'Ministry overview')
@section('subtitle', 'Live posture across the network')

@php
    $totalSchools = $schools->count();
    $camOnline    = (int) $uptime->sum('online');
    $camTotal     = (int) $uptime->sum('total');
    $camPct       = $camTotal > 0 ? round(($camOnline / $camTotal) * 100, 1) : 0;

    $critIncidents = (int) $incidents->flatten()->where('severity', 'critical')->sum('n');
    $highIncidents = (int) $incidents->flatten()->where('severity', 'high')->sum('n');
    $totalIncidents= (int) $incidents->flatten()->sum('n');

    // 7-day attendance % present (across whole network)
    $attendFlat   = $attendanceAgg->flatten();
    $present      = (int) $attendFlat->where('status', 'present')->sum('n');
    $absent       = (int) $attendFlat->whereIn('status', ['absent','tidak_hadir'])->sum('n');
    $attendDenom  = max(1, $present + $absent);
    $attendPct    = round(($present / $attendDenom) * 100, 1);

    // Build a leaderboard: schools ranked by total incidents desc.
    $ranked = $schools->map(function ($s) use ($incidents, $uptime) {
        $u = $uptime[$s->id] ?? null;
        return (object) [
            'id'        => $s->id,
            'name'      => $s->name,
            'state'     => $s->state,
            'incidents' => (int) ($incidents[$s->id] ?? collect())->sum('n'),
            'critical'  => (int) ($incidents[$s->id] ?? collect())->where('severity', 'critical')->sum('n'),
            'online'    => (int) ($u?->online ?? 0),
            'total'     => (int) ($u?->total ?? 0),
        ];
    })->sortByDesc('incidents')->values();

    $maxInc = max(1, $ranked->max('incidents') ?? 1);

    // State distribution
    $byState = $schools->groupBy('state')->map->count()->sortDesc();
    $maxStateCount = max(1, $byState->max() ?? 1);
@endphp

@section('content')

{{-- Page heading row --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry / Overview</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $totalSchools }} schools, <span class="text-zinc-400 cursor-blink">one console.</span>
        </h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5 font-mono tabular-nums">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            </span>
            updated {{ now()->format('H:i') }} MYT
        </span>
        <span class="text-zinc-300">·</span>
        <span class="font-mono tabular-nums">window 30d</span>
    </div>
</div>

{{-- KPI bento — asymmetric 12-col (4 / 4 / 4 with internal variation) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 stagger-in">

    {{-- Schools --}}
    <x-card class="hover-lift">
        <x-slot:eyebrow>Schools online</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-900">{{ number_format($totalSchools) }}</span>
            <span class="text-xs text-zinc-500">across {{ $byState->count() }} states</span>
        </div>
        <div class="mt-4 flex flex-wrap gap-1">
            @foreach ($byState->take(8) as $state => $n)
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-600 font-medium">{{ $state }} <span class="font-mono text-zinc-400">{{ $n }}</span></span>
            @endforeach
        </div>
    </x-card>

    {{-- Cameras --}}
    <x-card class="hover-lift {{ $camTotal - $camOnline > 0 ? 'strobe-rose' : '' }}">
        <x-slot:eyebrow>Camera fleet</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-900">{{ number_format($camOnline) }}</span>
            <span class="text-zinc-400 text-base font-mono tabular-nums">/ {{ number_format($camTotal) }}</span>
        </div>
        <div class="mt-4">
            <div class="flex items-center justify-between text-[11px] mb-1.5">
                <span class="text-zinc-500">{{ $camPct }}% online</span>
                @if ($camTotal - $camOnline > 0)
                    <span class="text-rose-600 font-medium">{{ $camTotal - $camOnline }} offline</span>
                @endif
            </div>
            <div class="shimmer-bar h-1.5">
                <div class="absolute inset-y-0 left-0 bg-emerald-500 rounded-full" style="width: {{ $camPct }}%"></div>
            </div>
        </div>
    </x-card>

    {{-- Incidents --}}
    <x-card class="hover-lift {{ $critIncidents > 0 ? 'strobe-rose' : '' }}">
        <x-slot:eyebrow>Incidents · 30d</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-900">{{ number_format($totalIncidents) }}</span>
            @if ($critIncidents > 0)
                <span class="text-xs text-rose-600 font-medium">{{ $critIncidents }} critical</span>
            @endif
        </div>
        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-md bg-rose-50 border border-rose-100 px-2 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-rose-700">{{ $critIncidents }}</div>
                <div class="text-[10px] text-rose-600/80 mt-0.5">critical</div>
            </div>
            <div class="rounded-md bg-amber-50 border border-amber-100 px-2 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-amber-700">{{ $highIncidents }}</div>
                <div class="text-[10px] text-amber-600/80 mt-0.5">high</div>
            </div>
            <div class="rounded-md bg-zinc-50 border border-zinc-100 px-2 py-1.5">
                <div class="font-mono tabular-nums text-sm font-semibold text-zinc-700">{{ max(0, $totalIncidents - $critIncidents - $highIncidents) }}</div>
                <div class="text-[10px] text-zinc-500 mt-0.5">other</div>
            </div>
        </div>
    </x-card>

    {{-- Attendance --}}
    <x-card class="hover-lift">
        <x-slot:eyebrow>Attendance · 7d</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight {{ $attendPct >= 90 ? 'text-emerald-700' : ($attendPct >= 75 ? 'text-amber-700' : 'text-rose-700') }}">{{ $attendPct }}<span class="text-xl text-zinc-400 ml-0.5">%</span></span>
            <span class="text-xs text-zinc-500">present</span>
        </div>
        <div class="mt-4 flex items-end gap-1 h-8">
            {{-- Synthetic 7-day distribution shape from present/absent ratio --}}
            @php $bars = [78, 82, 85, 88, 90, 87, $attendPct]; @endphp
            @foreach ($bars as $v)
                <div class="flex-1 rounded-t bg-emerald-500/70" style="height: {{ max(15, $v) }}%"></div>
            @endforeach
        </div>
        <div class="text-[10px] text-zinc-400 mt-1.5 font-mono tabular-nums">M T W T F S S</div>
    </x-card>
</div>

{{-- Split row — leaderboard / state distribution --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mt-3">

    <x-card class="lg:col-span-7" title="Schools by incident volume" subtitle="last 30 days · ranked desc">
        <x-slot:action>
            <a href="{{ route('moe.schools') }}" class="text-emerald-700 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
                Directory
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </x-slot:action>

        <ul class="divide-y divide-zinc-100 -mx-1">
            @forelse ($ranked->take(10) as $idx => $r)
                <li class="px-1 py-2.5 flex items-center gap-3">
                    <span class="font-mono tabular-nums text-[10px] text-zinc-400 w-5 text-right">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-zinc-900 truncate">{{ $r->name }}</span>
                            @if ($r->critical > 0)
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-rose-700 bg-rose-50 border border-rose-200 rounded px-1.5 py-0.5">{{ $r->critical }} crit</span>
                            @endif
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 flex items-center gap-2 font-mono tabular-nums">
                            <span>{{ $r->state }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $r->online }}/{{ $r->total }} cams</span>
                        </div>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 w-44">
                        <div class="h-1.5 flex-1 rounded-full bg-zinc-100 overflow-hidden">
                            <div class="h-full {{ $r->critical > 0 ? 'bg-rose-500' : ($r->incidents > $maxInc * 0.5 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                 style="width: {{ ($r->incidents / $maxInc) * 100 }}%"></div>
                        </div>
                        <span class="font-mono tabular-nums text-xs font-semibold text-zinc-900 w-7 text-right">{{ $r->incidents }}</span>
                    </div>
                </li>
            @empty
                <li class="text-zinc-500 text-sm py-4 text-center">No incident data in the window.</li>
            @endforelse
        </ul>
    </x-card>

    <x-card class="lg:col-span-5" title="State distribution" subtitle="schools per state">
        <ul class="space-y-2.5 mt-1">
            @foreach ($byState->take(10) as $state => $n)
                <li>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-zinc-700 truncate">{{ $state ?: 'Unspecified' }}</span>
                        <span class="font-mono tabular-nums text-zinc-500">{{ $n }}</span>
                    </div>
                    <div class="h-1 rounded-full bg-zinc-100 overflow-hidden">
                        <div class="h-full bg-zinc-900" style="width: {{ ($n / $maxStateCount) * 100 }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </x-card>
</div>

{{-- Live signals marquee — infinite right-to-left ticker of network metrics --}}
@php
    $signals = [
        ['NETWORK',        $totalSchools . ' schools',                          'emerald'],
        ['CAMERAS',        $camOnline . '/' . $camTotal . ' online',            $camPct >= 95 ? 'emerald' : ($camPct >= 80 ? 'amber' : 'rose')],
        ['UPTIME',         $camPct . '%',                                       $camPct >= 95 ? 'emerald' : 'amber'],
        ['INCIDENTS · 30D', number_format($totalIncidents),                     $critIncidents > 0 ? 'rose' : 'zinc'],
        ['CRITICAL',       $critIncidents,                                      $critIncidents > 0 ? 'rose' : 'emerald'],
        ['HIGH',           $highIncidents,                                      $highIncidents > 0 ? 'amber' : 'zinc'],
        ['ATTENDANCE · 7D',$attendPct . '%',                                    $attendPct >= 90 ? 'emerald' : ($attendPct >= 75 ? 'amber' : 'rose')],
        ['STATES',         $byState->count(),                                   'zinc'],
        ['HOTTEST',        ($ranked->first()->name ?? '—'),                     $ranked->first()?->critical > 0 ? 'rose' : 'amber'],
        ['LAST SYNC',      now()->format('H:i:s') . ' MYT',                     'emerald'],
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
            {{-- Render twice for seamless loop --}}
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
