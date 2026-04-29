@extends('layouts.shell')
@section('title', 'Activity trends')
@section('subtitle', 'last ' . $days . ' days')

@php
    $byType = $trend->flatten()->groupBy('type')->map(fn($g) => (int) $g->sum('n'))->sortDesc();
    $totalEvents = (int) $byType->sum();

    $palette = ['bg-rose-500', 'bg-amber-500', 'bg-emerald-500', 'bg-sky-500', 'bg-violet-500', 'bg-zinc-500'];
    $typeColor = [];
    $idxC = 0;
    foreach ($byType->keys() as $t) {
        $typeColor[$t] = $palette[$idxC % count($palette)];
        $idxC++;
    }

    $daily = $trend->map(fn($rows, $day) => [
        'day'   => $day,
        'total' => (int) collect($rows)->sum('n'),
        'rows'  => $rows,
    ])->values();

    $maxDaily  = max(1, $daily->max('total') ?? 1);
    $maxSchool = max(1, $perSchool->max('n') ?? 1);
    $presets   = [7, 30, 90, 180];
@endphp

@section('content')

{{-- Heading + range picker --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry / Trends</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ number_format($totalEvents) }} events
            <span class="text-zinc-400">across the network.</span>
        </h1>
    </div>

    <div class="flex items-center gap-2">
        <div class="flex items-center p-1 rounded-lg bg-zinc-100">
            @foreach ($presets as $p)
                <a href="{{ route('moe.trends', ['days' => $p]) }}"
                   class="px-2.5 py-1 rounded-md text-xs font-medium transition
                          {{ $days === $p ? 'bg-white text-zinc-900 shadow-card' : 'text-zinc-500 hover:text-zinc-900' }}">
                    {{ $p }}d
                </a>
            @endforeach
        </div>
        <form class="flex items-center gap-1 text-xs">
            <input type="number" name="days" value="{{ $days }}" min="1" max="365"
                   class="w-16 bg-white border border-zinc-200 rounded-md px-2 py-1 text-center font-mono tabular-nums focus:outline-none focus:border-zinc-300"/>
            <button class="px-2.5 py-1 rounded-md bg-zinc-900 text-white font-medium hover:bg-zinc-800 transition">Apply</button>
        </form>
    </div>
</div>

{{-- Type mix ribbon --}}
<x-card class="mb-3">
    <x-slot:eyebrow>Event mix</x-slot:eyebrow>

    @if ($totalEvents > 0)
        <div class="flex h-2 rounded-full overflow-hidden bg-zinc-100 mt-1">
            @foreach ($byType as $type => $n)
                <div class="{{ $typeColor[$type] }}" style="width: {{ ($n / $totalEvents) * 100 }}%" title="{{ $type }}: {{ $n }}"></div>
            @endforeach
        </div>

        <ul class="flex flex-wrap gap-x-5 gap-y-1.5 mt-3 text-xs">
            @foreach ($byType as $type => $n)
                <li class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-sm {{ $typeColor[$type] }}"></span>
                    <span class="text-zinc-700 font-medium uppercase tracking-wide text-[11px]">{{ $type }}</span>
                    <span class="text-zinc-400 font-mono tabular-nums">{{ $n }}</span>
                    <span class="text-zinc-300">·</span>
                    <span class="text-zinc-500 font-mono tabular-nums">{{ round(($n / $totalEvents) * 100, 1) }}%</span>
                </li>
            @endforeach
        </ul>
    @else
        <div class="text-sm text-zinc-500 py-2">No events recorded in this window.</div>
    @endif
</x-card>

{{-- Daily volume chart --}}
<x-card class="mb-3" title="Daily volume" subtitle="bars are total events per day · stacked by type">
    @if ($daily->count() > 0)
        <div class="relative">
            <div class="flex items-end gap-[2px] h-40 mt-2 mb-2">
                @foreach ($daily as $d)
                    @php $heightPct = ($d['total'] / $maxDaily) * 100; @endphp
                    <div class="group flex-1 flex flex-col-reverse h-full relative" title="{{ $d['day'] }}: {{ $d['total'] }}">
                        @foreach ($d['rows'] as $row)
                            @php $segHeight = $d['total'] > 0 ? ($row->n / $d['total']) * $heightPct : 0; @endphp
                            <div class="{{ $typeColor[$row->type] ?? 'bg-zinc-400' }} w-full" style="height: {{ $segHeight }}%"></div>
                        @endforeach

                        <div class="absolute -top-1 left-1/2 -translate-x-1/2 -translate-y-full opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                            <div class="bg-zinc-900 text-white text-[10px] font-mono tabular-nums rounded px-1.5 py-1 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($d['day'])->format('M d') }} · {{ $d['total'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-between text-[10px] font-mono tabular-nums text-zinc-400 mt-1">
                <span>{{ \Carbon\Carbon::parse($daily->first()['day'])->format('M d') }}</span>
                <span>{{ \Carbon\Carbon::parse($daily->last()['day'])->format('M d') }}</span>
            </div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-zinc-50 border border-zinc-200 mb-2">
                <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M7 21V11M12 21V5M17 21v-7"/>
                </svg>
            </div>
            <div class="text-sm text-zinc-500">No daily activity in this window.</div>
        </div>
    @endif
</x-card>

{{-- Bottom split — per-school + type leaderboard --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

    <x-card class="lg:col-span-7" title="Schools by event volume" subtitle="ordered descending">
        @if ($perSchool->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($perSchool->take(12) as $idx => $row)
                    <li class="px-1 py-2.5 flex items-center gap-3">
                        <span class="font-mono tabular-nums text-[10px] text-zinc-400 w-5 text-right">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-sm font-medium text-zinc-900 truncate flex-1">{{ $schoolNames[$row->school_id] ?? '—' }}</span>

                        <div class="flex items-center gap-2 w-44">
                            <div class="h-1.5 flex-1 rounded-full bg-zinc-100 overflow-hidden">
                                <div class="h-full bg-zinc-900" style="width: {{ ($row->n / $maxSchool) * 100 }}%"></div>
                            </div>
                            <span class="font-mono tabular-nums text-xs font-semibold text-zinc-900 w-10 text-right">{{ $row->n }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-sm text-zinc-500 py-4">No school activity in window.</div>
        @endif
    </x-card>

    <x-card class="lg:col-span-5" title="Type leaderboard">
        @if ($byType->count() > 0)
            @php $maxType = max(1, $byType->first()); @endphp
            <ul class="space-y-3 mt-1">
                @foreach ($byType as $type => $n)
                    <li>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-sm {{ $typeColor[$type] }}"></span>
                                <span class="font-medium uppercase tracking-wide text-zinc-700">{{ $type }}</span>
                            </span>
                            <span class="font-mono tabular-nums text-zinc-500">{{ number_format($n) }}</span>
                        </div>
                        <div class="h-1 rounded-full bg-zinc-100 overflow-hidden">
                            <div class="h-full {{ $typeColor[$type] }}" style="width: {{ ($n / $maxType) * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-sm text-zinc-500 py-4">No type data.</div>
        @endif
    </x-card>
</div>

@endsection
