@extends('layouts.shell')
@section('title', 'Timetables')
@section('subtitle', 'view-only · filter to focus a class or teacher')

@php
    $days = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Timetables</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ count($schedules) }} entries
            <span class="text-zinc-400">in this view.</span>
        </h1>
    </div>
    <a href="{{ route('school.schedule') }}" class="text-sm text-emerald-700 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
        Manage in Schedule
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
</div>

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <select name="class_id" class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All classes</option>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected($classId == $c->id)>{{ $c->grade->name }} · {{ $c->name }}</option>
        @endforeach
    </select>
    <select name="teacher_id" class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All teachers</option>
        @foreach ($teachers as $t)
            <option value="{{ $t->id }}" @selected($teacherId == $t->id)>{{ $t->name }}</option>
        @endforeach
    </select>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Filter
    </button>
</form>

@if (count($schedules) > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
        <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-100 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">
            <div class="col-span-1">Day</div>
            <div class="col-span-1">Period</div>
            <div class="col-span-3">Class</div>
            <div class="col-span-3">Teacher</div>
            <div class="col-span-3">Subject</div>
            <div class="col-span-1 text-right">Room</div>
        </div>
        @foreach ($schedules as $s)
            <div class="grid grid-cols-12 gap-3 px-4 py-2.5 border-b border-zinc-50 last:border-b-0 items-center text-sm">
                <div class="col-span-1 font-mono text-xs text-zinc-700">{{ $days[$s->day_of_week] ?? 'D'.$s->day_of_week }}</div>
                <div class="col-span-1 font-mono text-xs text-zinc-500">{{ $s->period?->label }}</div>
                <div class="col-span-3 font-medium text-zinc-900 truncate">{{ $s->schoolClass?->name }}</div>
                <div class="col-span-3 text-zinc-700 truncate">{{ $s->teacher?->name ?? '—' }}</div>
                <div class="col-span-3 text-zinc-700 truncate">{{ $s->subject?->name ?? '—' }}</div>
                <div class="col-span-1 text-right font-mono text-xs text-zinc-500">{{ $s->room ?: '—' }}</div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No schedule entries match this filter</div>
        <div class="text-xs text-zinc-500 mt-1">Try a different class or teacher above.</div>
    </div>
@endif

@endsection
