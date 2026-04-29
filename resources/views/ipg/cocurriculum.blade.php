@extends('layouts.shell')
@section('title', 'Co-curriculum')
@section('subtitle', 'Kokurikulum · mandatory PISMP units')

@php
    $totalUnits = $participations->sum('units_earned');
    $rolePill = [
        'president'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'vice_president' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'secretary'      => 'text-sky-700 bg-sky-50 border-sky-200',
        'member'         => 'text-zinc-700 bg-zinc-50 border-zinc-200',
    ];
    $categoryDot = [
        'sukan'      => 'bg-emerald-500',
        'persatuan'  => 'bg-sky-500',
        'kelab'      => 'bg-amber-500',
        'beruniform' => 'bg-rose-500',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics / Co-curriculum</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $activities->count() }} {{ Str::plural('activity', $activities->count()) }},
            <span class="text-zinc-400">{{ $participations->count() }} recent {{ Str::plural('participation', $participations->count()) }}.</span>
        </h1>
    </div>
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">Pick a campus to view co-curriculum</div>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
        <x-card class="lg:col-span-5" title="Activities" subtitle="campus-wide kokurikulum portfolio">
            @if ($activities->count() > 0)
                <ul class="divide-y divide-zinc-100 -mx-1">
                    @foreach ($activities as $a)
                        <li class="px-1 py-2.5 flex items-center gap-3">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $categoryDot[$a->category] ?? 'bg-zinc-400' }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-zinc-900 truncate">{{ $a->name }}</div>
                                <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                                    <span class="uppercase tracking-wide">{{ $a->category }}</span>
                                    <span class="text-zinc-300">·</span>
                                    <span>{{ $a->code }}</span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="font-mono tabular-nums text-sm font-semibold text-zinc-900">{{ $a->participations_count }}</div>
                                <div class="text-[10px] text-zinc-400">members</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-xs text-zinc-500 py-2">No activities defined yet.</div>
            @endif
        </x-card>

        <x-card class="lg:col-span-7" title="Recent participations" subtitle="latest 20 · across all activities">
            <x-slot:action>
                <span class="font-mono tabular-nums text-emerald-700 font-medium">{{ $totalUnits }} units issued</span>
            </x-slot:action>

            @if ($participations->count() > 0)
                <ul class="divide-y divide-zinc-100 -mx-1">
                    @foreach ($participations as $p)
                        @php $initial = strtoupper(mb_substr($p->trainee?->name ?? '?', 0, 1)); @endphp
                        <li class="px-1 py-2 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-[11px] font-semibold shrink-0">{{ $initial }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-zinc-900 truncate">{{ $p->trainee?->name }}</div>
                                <div class="text-[11px] text-zinc-500 mt-0.5 truncate">
                                    {{ $p->activity?->name }}
                                    @if ($p->semester) <span class="text-zinc-300">·</span> <span class="font-mono">{{ $p->semester->code }}</span> @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $rolePill[$p->role] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                                {{ str_replace('_', ' ', $p->role) }}
                            </span>
                            <div class="text-right shrink-0">
                                <div class="font-mono tabular-nums text-sm font-semibold text-emerald-700">+{{ $p->units_earned }}</div>
                                @if ($p->evaluation_score)
                                    <div class="text-[10px] text-zinc-400">score {{ $p->evaluation_score }}</div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-xs text-zinc-500 py-2">No participations recorded yet.</div>
            @endif
        </x-card>
    </div>
@endif

@endsection
