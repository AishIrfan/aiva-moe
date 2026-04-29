@extends('layouts.shell')
@section('title', 'Warning letters')
@section('subtitle', '≥ ' . $threshold . ' incidents in 30 days')

@php
    $subPages = [
        ['Today',           'school.attendance'],
        ['Follow-up',       'school.attendance-follow-up'],
        ['Records',         'school.attendance-records'],
        ['Monthly summary', 'school.attendance-monthly-summary'],
        ['Warning letters', 'school.attendance-warning-letters', true],
    ];

    $maxN = is_iterable($offenders) ? max(1, collect($offenders)->max('n') ?? 1) : 1;
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ count($offenders ?? []) }} students
            <span class="text-zinc-400">flagged for follow-up.</span>
        </h1>
    </div>
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
        <label class="text-zinc-500">Threshold</label>
        <input type="number" name="threshold" value="{{ $threshold }}" min="1"
               class="w-20 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums text-center focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <span class="text-zinc-400">incidents · last 30 days</span>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Recalculate
    </button>
</form>

@if (count($offenders ?? []) > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($offenders as $o)
            @php
                $name = $students[$o->student_id]->name ?? '—';
                $initial = strtoupper(mb_substr($name, 0, 1));
                $tone = $o->n >= $threshold * 2 ? 'rose' : 'amber';
            @endphp
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $name }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">student id {{ $o->student_id }}</div>
                </div>
                <div class="flex items-center gap-2 w-40">
                    <div class="h-1.5 flex-1 rounded-full bg-zinc-100 overflow-hidden">
                        <div class="h-full {{ $tone === 'rose' ? 'bg-rose-500' : 'bg-amber-500' }}" style="width: {{ ($o->n / $maxN) * 100 }}%"></div>
                    </div>
                    <span class="font-mono tabular-nums text-sm font-semibold {{ $tone === 'rose' ? 'text-rose-700' : 'text-amber-700' }} w-7 text-right">{{ $o->n }}</span>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 mb-3">
            <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">No students flagged</div>
        <div class="text-xs text-zinc-500 mt-1">No one has hit the {{ $threshold }}-incident threshold in the last 30 days.</div>
    </div>
@endif

@endsection
