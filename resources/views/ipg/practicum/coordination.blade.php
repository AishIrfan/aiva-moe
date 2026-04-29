@extends('layouts.shell')
@section('title', 'School coordination')
@section('subtitle', 'placement letters · principal acknowledgements')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / School coordination</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $letters->count() }} {{ Str::plural('letter', $letters->count()) }}
            <span class="text-zinc-400">on file.</span>
        </h1>
    </div>
</div>

@if ($letters->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($letters as $l)
            @php $acknowledged = $l->acknowledged_at !== null; @endphp
            <div class="px-4 py-3.5 flex items-start gap-3">
                <span class="w-9 h-9 rounded-md bg-zinc-100 text-zinc-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-zinc-900">{{ str_replace('_', ' ', $l->kind) }}</span>
                        <span class="text-[10px] font-mono tabular-nums text-zinc-400">→ {{ $l->placement?->hostSchool?->name }}</span>
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex flex-wrap items-center gap-x-3 gap-y-0.5">
                        <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Trainee</span> <span class="text-zinc-900 normal-case">{{ $l->placement?->trainee?->name }}</span></span>
                        @if ($l->principal_name)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Principal</span> <span class="text-zinc-900 normal-case">{{ $l->principal_name }}</span></span>
                        @endif
                        @if ($l->sent_at)
                            <span><span class="text-[10px] uppercase tracking-wider text-zinc-400">Sent</span> {{ $l->sent_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                    @if ($l->body)
                        <div class="text-xs text-zinc-700 leading-relaxed mt-2 max-w-3xl">{{ $l->body }}</div>
                    @endif
                </div>
                <div class="text-right shrink-0">
                    @if ($acknowledged)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-emerald-700 bg-emerald-50 border-emerald-200">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            acknowledged
                        </span>
                        <div class="text-[10px] text-zinc-400 mt-1 font-mono tabular-nums">{{ $l->acknowledged_at->format('M j') }}</div>
                    @else
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-amber-700 bg-amber-50 border-amber-200">
                            <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                            pending
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No letters yet</div>
        <div class="text-xs text-zinc-500 mt-1">Placement letters auto-generate when a placement is scheduled.</div>
    </div>
@endif

@endsection
