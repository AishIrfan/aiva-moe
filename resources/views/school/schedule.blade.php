@extends('layouts.shell')
@section('title', 'Schedule')
@section('subtitle', 'Jadual kelas · ' . $stats['total'] . ' entries')

@php
    $days = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];

    $kindPill = [
        'regular'     => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'replacement' => 'text-amber-700 bg-amber-50 border-amber-200',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Schedule</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $stats['total'] }} entries
            <span class="text-zinc-400">on the timetable.</span>
        </h1>
    </div>
    <a href="{{ route('school.schedule.export') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-zinc-700 text-sm hover:border-zinc-300 transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
        </svg>
        Export CSV
    </a>
</div>

{{-- KPI strip --}}
<div class="grid grid-cols-3 gap-2 mb-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Total</div>
        <div class="font-mono tabular-nums text-2xl font-semibold tracking-tight">{{ $stats['total'] }}</div>
    </div>
    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700/70 mb-1">Regular</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-emerald-700">{{ $stats['regular'] }}</div>
    </div>
    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-amber-700/70 mb-1">Replacements</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-amber-700">{{ $stats['replacements'] }}</div>
    </div>
</div>

{{-- Tab nav --}}
<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach (['master','class','teacher','room','replacement'] as $t)
        <a href="?tab={{ $t }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ $tab === $t ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">
            {{ ucfirst($t) }}
        </a>
    @endforeach
</nav>

{{-- Add entry --}}
<x-card class="mb-3" title="Add entry" subtitle="register a class · teacher · subject for a period slot">
    <form method="POST" action="{{ route('school.schedule.store') }}" class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">
        @csrf
        <select name="school_class_id" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Class…</option>
            @foreach ($classes as $c)<option value="{{ $c->id }}">{{ $c->grade->name }} · {{ $c->name }}</option>@endforeach
        </select>
        <select name="teacher_id" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Teacher…</option>
            @foreach ($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
        </select>
        <select name="subject_id" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Subject…</option>
            @foreach ($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <select name="period_id" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Period…</option>
            @foreach ($periods as $p)<option value="{{ $p->id }}">{{ $p->label }}</option>@endforeach
        </select>
        <select name="day_of_week" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            @foreach ($days as $n => $lbl)<option value="{{ $n }}">{{ $lbl }}</option>@endforeach
        </select>
        <input name="room" placeholder="Room" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400 font-mono text-xs"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-4 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Add</button>
    </form>
</x-card>

@if (count($schedules) > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
        <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-100 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">
            <div class="col-span-1">Day</div>
            <div class="col-span-1">Period</div>
            <div class="col-span-2">Class</div>
            <div class="col-span-2">Teacher</div>
            <div class="col-span-2">Subject</div>
            <div class="col-span-1">Room</div>
            <div class="col-span-2">Kind</div>
            <div class="col-span-1 text-right"></div>
        </div>
        @foreach ($schedules as $s)
            <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-50 last:border-b-0 items-center text-sm">
                <div class="col-span-1 font-mono text-xs text-zinc-700">{{ $days[$s->day_of_week] ?? 'D'.$s->day_of_week }}</div>
                <div class="col-span-1 font-mono text-xs text-zinc-500">{{ $s->period?->label }}</div>
                <div class="col-span-2 font-medium text-zinc-900 truncate">{{ $s->schoolClass?->name }}</div>
                <div class="col-span-2 text-zinc-700 truncate">{{ $s->teacher?->name ?? '—' }}</div>
                <div class="col-span-2 text-zinc-700 truncate">{{ $s->subject?->name ?? '—' }}</div>
                <div class="col-span-1 font-mono text-xs text-zinc-500">{{ $s->room ?: '—' }}</div>
                <div class="col-span-2">
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $kindPill[$s->kind] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                        {{ $s->kind ?? 'regular' }}
                    </span>
                </div>
                <div class="col-span-1 text-right">
                    <form method="POST" action="{{ route('school.schedule.destroy', $s) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-xs text-rose-600 hover:text-rose-800 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No schedule entries yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use the form above to add the first slot.</div>
    </div>
@endif

@endsection
