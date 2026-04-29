@extends('layouts.shell')
@section('title', 'Alerts & incidents')
@section('subtitle', $school->name)

@php
    $sevDot = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'high'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
        'low'      => 'bg-emerald-500',
    ];
    $sevPill = [
        'critical' => 'text-rose-700 bg-rose-50 border-rose-200',
        'warn'     => 'text-amber-700 bg-amber-50 border-amber-200',
        'high'     => 'text-amber-700 bg-amber-50 border-amber-200',
        'info'     => 'text-sky-700 bg-sky-50 border-sky-200',
        'low'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
    ];
    $statusPill = [
        'open'         => 'text-rose-700 bg-rose-50 border-rose-200',
        'acknowledged' => 'text-amber-700 bg-amber-50 border-amber-200',
        'escalated'    => 'text-rose-800 bg-rose-100 border-rose-300',
        'closed'       => 'text-zinc-600 bg-zinc-50 border-zinc-200',
    ];

    // Counts per status across the current page (for the strip).
    $byStatus = $events->getCollection()->groupBy('status')->map->count();
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Alerts</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $events->total() }} events,
            <span class="text-zinc-400">triage queue.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">page {{ $events->currentPage() }} / {{ max(1, $events->lastPage()) }}</div>
</div>

{{-- Status strip --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
    @foreach (['open' => 'Open', 'acknowledged' => 'Acknowledged', 'escalated' => 'Escalated', 'closed' => 'Closed'] as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}"
           class="rounded-xl border px-3 py-2.5 transition
                  {{ request('status') === $key ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white hover:border-zinc-300' }}">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold {{ request('status') === $key ? 'text-white/60' : 'text-zinc-400' }}">{{ $label }}</div>
            <div class="font-mono tabular-nums text-xl font-semibold mt-0.5">{{ $byStatus[$key] ?? 0 }}</div>
        </a>
    @endforeach
</div>

{{-- Filter bar --}}
<form class="flex flex-wrap items-center gap-2 mb-4 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <select name="status"
            class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All statuses</option>
        @foreach (['open','acknowledged','escalated','closed'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="severity"
            class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All severities</option>
        @foreach (['info','warn','critical'] as $s)
            <option value="{{ $s }}" @selected(request('severity')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>

    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Filter
    </button>

    @if (request('status') || request('severity'))
        <a href="{{ route('school.alerts') }}" class="text-xs text-zinc-500 hover:text-zinc-900 underline underline-offset-4">clear</a>
    @endif
</form>

{{-- Events feed (card-list, not table) --}}
@if ($events->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100">
        @foreach ($events as $e)
            <div class="px-4 py-3.5 flex items-start gap-3">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>

                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-zinc-900">{{ $e->title }}</span>
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $sevPill[$e->severity] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                            {{ $e->severity }}
                        </span>
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$e->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                            {{ $e->status }}
                        </span>
                    </div>
                    <div class="text-[11px] text-zinc-500 mt-1 font-mono tabular-nums flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span class="uppercase tracking-wide">{{ $e->type }}</span>
                        <span class="text-zinc-300">·</span>
                        <span>{{ $e->created_at?->diffForHumans() }}</span>
                        @if ($e->assigned_to)
                            <span class="text-zinc-300">·</span>
                            <span class="normal-case">assigned to <span class="text-zinc-700">{{ $e->assigned_to }}</span></span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    @if ($e->status === 'open')
                        <form method="POST" action="{{ route('school.alerts.acknowledge', $e) }}">
                            @csrf
                            <button class="text-xs font-medium text-zinc-700 hover:text-zinc-900 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-md px-2.5 py-1 transition">
                                Ack
                            </button>
                        </form>
                    @endif
                    @if (in_array($e->status, ['open','acknowledged']))
                        <form method="POST" action="{{ route('school.alerts.escalate', $e) }}">
                            @csrf
                            <button class="text-xs font-medium text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-md px-2.5 py-1 transition">
                                Escalate
                            </button>
                        </form>
                    @endif
                    @if ($e->status !== 'closed')
                        <form method="POST" action="{{ route('school.alerts.close', $e) }}">
                            @csrf
                            <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">
                                Close
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $events->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 mb-3">
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">All clear</div>
        <div class="text-xs text-zinc-500 mt-1">No events match the current filter.</div>
    </div>
@endif

@endsection
