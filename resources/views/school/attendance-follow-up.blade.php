@extends('layouts.shell')
@section('title', 'Follow-up')
@section('subtitle', 'absences & lates · last 7 days')

@php
    $statusPill = [
        'absent' => 'text-rose-700 bg-rose-50 border-rose-200',
        'late'   => 'text-amber-700 bg-amber-50 border-amber-200',
    ];

    $subPages = [
        ['Today',           'school.attendance'],
        ['Follow-up',       'school.attendance-follow-up',       true],
        ['Records',         'school.attendance-records'],
        ['Monthly summary', 'school.attendance-monthly-summary'],
        ['Warning letters', 'school.attendance-warning-letters'],
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $rows->total() }} cases
            <span class="text-zinc-400">need a follow-up.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">page {{ $rows->currentPage() }} / {{ max(1, $rows->lastPage()) }}</div>
</div>

<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($subPages as $sp)
        <a href="{{ route($sp[1]) }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ ($sp[2] ?? false) ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">{{ $sp[0] }}</a>
    @endforeach
</nav>

@if ($rows->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($rows as $r)
            @php $initial = strtoupper(mb_substr($r->student?->name ?? '?', 0, 1)); @endphp
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $r->student?->name ?? '—' }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                        {{ $r->date instanceof \Carbon\Carbon ? $r->date->format('D · M j') : $r->date }}
                    </div>
                </div>
                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$r->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $r->status }}
                </span>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $rows->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 mb-3">
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">Nothing to follow up</div>
        <div class="text-xs text-zinc-500 mt-1">No absences or lates in the last 7 days.</div>
    </div>
@endif

@endsection
