@extends('layouts.shell')
@section('title', 'Attendance')
@section('subtitle', 'daily roll · today')

@php
    $subPages = [
        ['Today',           'ipg.attendance', true],
        ['Follow-up',       'ipg.attendance-follow-up'],
        ['Records',         'ipg.attendance-records'],
        ['Monthly summary', 'ipg.attendance-monthly-summary'],
        ['Warning letters', 'ipg.attendance-warning-letters'],
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Today's roll <span class="text-zinc-400 cursor-blink">unmarked.</span>
        </h1>
    </div>
    <input type="date" value="{{ now()->toDateString() }}"
           class="bg-white border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:border-zinc-300"/>
</div>

<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($subPages as $sp)
        <a href="{{ route($sp[1]) }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ ($sp[2] ?? false) ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">{{ $sp[0] }}</a>
    @endforeach
</nav>

<div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-3">
    @foreach (['attendance %', 'present', 'late', 'leave / mc', 'absent'] as $label)
        <div class="rounded-xl border border-zinc-200 bg-white p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">{{ $label }}</div>
            <div class="font-mono tabular-nums text-2xl font-semibold tracking-tight text-zinc-300">—</div>
        </div>
    @endforeach
</div>

<div class="bg-white border border-zinc-200 rounded-xl shadow-card p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No attendance pipeline yet</div>
    <div class="text-xs text-zinc-500 mt-1">Trainee attendance feeds in once the campus marking flow is wired (Phase 4).</div>
</div>

@endsection
