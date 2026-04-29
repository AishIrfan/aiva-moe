@extends('layouts.shell')
@section('title', 'Records')
@section('subtitle', $from . ' → ' . $to)

@php
    $statusPill = [
        'present' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'late'    => 'text-amber-700 bg-amber-50 border-amber-200',
        'absent'  => 'text-rose-700 bg-rose-50 border-rose-200',
        'leave'   => 'text-sky-700 bg-sky-50 border-sky-200',
        'mc'      => 'text-sky-700 bg-sky-50 border-sky-200',
    ];

    $subPages = [
        ['Today',           'school.attendance'],
        ['Follow-up',       'school.attendance-follow-up'],
        ['Records',         'school.attendance-records', true],
        ['Monthly summary', 'school.attendance-monthly-summary'],
        ['Warning letters', 'school.attendance-warning-letters'],
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $rows->total() }} records
            <span class="text-zinc-400">in window.</span>
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

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="flex items-center gap-2 text-xs">
        <label class="text-zinc-500">From</label>
        <input type="date" name="from" value="{{ $from }}"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <label class="text-zinc-500 ml-2">To</label>
        <input type="date" name="to" value="{{ $to }}"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Apply
    </button>
</form>

@if ($rows->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($rows as $r)
            <div class="px-4 py-2.5 flex items-center gap-3">
                <span class="text-[11px] text-zinc-500 font-mono tabular-nums w-24 shrink-0">
                    {{ $r->date instanceof \Carbon\Carbon ? $r->date->format('M j · Y') : $r->date }}
                </span>
                <span class="text-sm text-zinc-900 truncate flex-1">{{ $r->student?->name ?? '—' }}</span>
                <span class="text-[10px] uppercase tracking-wider text-zinc-400 font-mono w-16 shrink-0 hidden sm:block">{{ $r->source ?? 'system' }}</span>
                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$r->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $r->status }}
                </span>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $rows->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No records in this window</div>
        <div class="text-xs text-zinc-500 mt-1">Try widening the date range.</div>
    </div>
@endif

@endsection
