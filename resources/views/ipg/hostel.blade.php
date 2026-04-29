@extends('layouts.shell')
@section('title', 'Hostel · Asrama')
@section('subtitle', 'room assignments · capacity')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Trainees / Hostel</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            @if ($campus)
                {{ $occupied }}<span class="text-zinc-400">/{{ $totalCapacity }}</span> beds
                <span class="text-zinc-400">occupied.</span>
            @else
                Pick a campus <span class="text-zinc-400 cursor-blink">first.</span>
            @endif
        </h1>
    </div>
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No campus selected</div>
    </div>
@else
    {{-- Capacity strip --}}
    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Blocks</div>
            <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight">{{ $blocks->count() }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Capacity</div>
            <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight">{{ $totalCapacity }}</div>
        </div>
        <div class="rounded-xl border {{ $occupancyPct >= 90 ? 'border-rose-100 bg-rose-50/60' : ($occupancyPct >= 70 ? 'border-amber-100 bg-amber-50/60' : 'border-emerald-100 bg-emerald-50/60') }} p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold {{ $occupancyPct >= 90 ? 'text-rose-700/70' : ($occupancyPct >= 70 ? 'text-amber-700/70' : 'text-emerald-700/70') }} mb-1">Occupancy</div>
            <div class="font-mono tabular-nums text-3xl font-semibold {{ $occupancyPct >= 90 ? 'text-rose-700' : ($occupancyPct >= 70 ? 'text-amber-700' : 'text-emerald-700') }}">{{ $occupancyPct }}<span class="text-base text-zinc-400">%</span></div>
        </div>
    </div>

    {{-- Blocks + rooms grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
        @foreach ($blocks as $block)
            @php
                $blockOccupied = $block->rooms->sum('occupants_count');
                $blockCapacity = $block->rooms->sum('capacity');
                $blockPct = $blockCapacity > 0 ? round(($blockOccupied / $blockCapacity) * 100, 1) : 0;
            @endphp
            <x-card class="hover-lift" :title="$block->name" :subtitle="$block->code . ' · ' . $blockOccupied . '/' . $blockCapacity . ' beds'">
                <x-slot:action>
                    <span class="font-mono tabular-nums text-xs text-zinc-700">{{ $blockPct }}%</span>
                </x-slot:action>

                <div class="shimmer-bar h-1.5 mt-1 mb-3">
                    <div class="absolute inset-y-0 left-0 rounded-full {{ $blockPct >= 90 ? 'bg-rose-500' : ($blockPct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $blockPct }}%"></div>
                </div>

                <ul class="grid grid-cols-2 gap-1.5">
                    @foreach ($block->rooms as $room)
                        @php $full = $room->occupants_count >= $room->capacity; @endphp
                        <li class="flex items-center justify-between rounded-md border border-zinc-200 bg-zinc-50/60 px-2 py-1.5 text-xs">
                            <span class="font-mono tabular-nums text-zinc-700">{{ $room->room_number }}</span>
                            <span class="font-mono tabular-nums {{ $full ? 'text-rose-700' : 'text-zinc-500' }}">{{ $room->occupants_count }}/{{ $room->capacity }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endforeach
    </div>

    {{-- Assignments list --}}
    <x-card title="Active assignments" subtitle="trainees currently assigned to rooms">
        @if ($assignments->count() > 0)
            <div class="-mx-5 -mb-5 border-t border-zinc-100 divide-y divide-zinc-100">
                @foreach ($assignments as $a)
                    @php $initial = strtoupper(mb_substr($a->trainee?->name ?? '?', 0, 1)); @endphp
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">{{ $initial }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-zinc-900 truncate">{{ $a->trainee?->name }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                                <span>{{ $a->trainee?->trainee_number }}</span>
                                <span class="text-zinc-300">·</span>
                                <span class="normal-case">{{ $a->trainee?->cohort?->display_name }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-[10px] uppercase tracking-wider text-zinc-400">Room</div>
                            <div class="font-mono tabular-nums text-sm font-semibold text-zinc-900">{{ $a->room?->room_number }}</div>
                            <div class="text-[10px] text-zinc-400">{{ $a->room?->block?->name }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-xs text-zinc-500 py-4 text-center">No active assignments yet.</div>
        @endif
    </x-card>
@endif

@endsection
