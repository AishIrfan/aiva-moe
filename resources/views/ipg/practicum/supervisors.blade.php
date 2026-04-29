@extends('layouts.shell')
@section('title', 'Practicum supervisors')
@section('subtitle', 'Pensyarah Penyelia · campus assignments')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / Supervisors</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $supervisors?->count() ?? 0 }} pensyarah <span class="text-zinc-400">on the panel.</span>
        </h1>
    </div>
</div>

@if (! $campus)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">Pick a campus</div>
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($supervisors as $p)
            @php $initial = strtoupper(mb_substr($p->name, 0, 1)); @endphp
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-9 h-9 rounded-full {{ $p->is_practicum_coordinator ? 'bg-emerald-600' : 'bg-zinc-100' }} {{ $p->is_practicum_coordinator ? 'text-white' : 'text-zinc-700' }} flex items-center justify-center text-sm font-semibold shrink-0">{{ $initial }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-zinc-900">{{ $p->name }}</span>
                        @if ($p->is_practicum_coordinator)
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-emerald-700 bg-emerald-50 border-emerald-200">
                                Penyelaras Praktikum
                            </span>
                        @endif
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                        <span>{{ $p->staff_number ?: '—' }}</span>
                        @if ($p->specialization)
                            <span class="text-zinc-300">·</span>
                            <span class="normal-case">{{ $p->specialization }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="font-mono tabular-nums text-sm font-semibold text-zinc-900">{{ $p->assigned_count }}</div>
                    <div class="text-[10px] text-zinc-400">trainees assigned</div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
