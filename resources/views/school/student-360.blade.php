@extends('layouts.shell')
@section('title', $student->name)
@section('subtitle', 'Student 360 · #' . $student->student_number)

@php
    $initial = strtoupper(mb_substr($student->name, 0, 1));
    $cls     = $student->activeEnrollment?->schoolClass;

    $statusPill = [
        'active'     => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'transferred'=> 'text-amber-700 bg-amber-50 border-amber-200',
        'graduated'  => 'text-sky-700 bg-sky-50 border-sky-200',
        'inactive'   => 'text-zinc-700 bg-zinc-50 border-zinc-200',
    ];

    $attDot = [
        'present' => 'bg-emerald-500',
        'late'    => 'bg-amber-500',
        'absent'  => 'bg-rose-500',
        'leave'   => 'bg-sky-500',
        'mc'      => 'bg-sky-500',
    ];

    $sevDot = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
    ];
@endphp

@section('content')

{{-- Hero card --}}
<div class="bg-white border border-zinc-200 rounded-xl shadow-card p-5 md:p-6 mb-3">
    <div class="flex items-start gap-5">
        <span class="w-16 h-16 rounded-full bg-zinc-900 text-white flex items-center justify-center text-2xl font-semibold tracking-tight shrink-0">{{ $initial }}</span>
        <div class="min-w-0 flex-1">
            <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold">Student profile</div>
            <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-zinc-900 mt-0.5">{{ $student->name }}</h1>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-xs">
                <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">Student #</span><div class="font-mono tabular-nums text-zinc-900">{{ $student->student_number }}</div></div>
                <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">IC</span><div class="font-mono tabular-nums text-zinc-900">{{ $student->ic_number ?: '—' }}</div></div>
                <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">Gender</span><div class="text-zinc-900">{{ $student->gender ?: '—' }}</div></div>
                <div><span class="text-[10px] uppercase tracking-wider text-zinc-400">Class</span><div class="text-zinc-900">{{ $cls ? ($cls->grade?->name . ' · ' . $cls->name) : 'unenrolled' }}</div></div>
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-zinc-400">Status</span>
                    <div class="mt-0.5">
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$student->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">{{ $student->status }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top bento — guardians / attendance feed --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-3">
    <x-card class="lg:col-span-5" title="Guardians" subtitle="contacts on record">
        @if ($student->guardians->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($student->guardians as $g)
                    @php $gi = strtoupper(mb_substr($g->name, 0, 1)); @endphp
                    <li class="px-1 py-2 flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-[11px] font-semibold shrink-0">{{ $gi }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-zinc-900 truncate">{{ $g->name }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                                {{ $g->relationship ?: 'guardian' }}
                                @if ($g->phone) <span class="text-zinc-300">·</span> {{ $g->phone }} @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No guardians on record.</div>
        @endif
    </x-card>

    <x-card class="lg:col-span-7" title="Attendance · last 30" subtitle="newest first">
        @if (count($recentAttendance) > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($recentAttendance as $a)
                    <li class="px-1 py-1.5 flex items-center gap-3">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $attDot[$a->status] ?? 'bg-zinc-400' }}"></span>
                        <span class="text-xs text-zinc-700 font-mono tabular-nums w-24">{{ $a->date instanceof \Carbon\Carbon ? $a->date->format('D · M j') : $a->date }}</span>
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-zinc-500">{{ $a->status }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No attendance records yet.</div>
        @endif
    </x-card>
</div>

{{-- Add note / log incident --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
    <x-card title="Add note">
        <form method="POST" action="{{ route('school.students.notes.store', $student) }}" class="space-y-2 text-sm">
            @csrf
            <select name="category"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="general">General</option>
                <option value="academic">Academic</option>
                <option value="behavioral">Behavioral</option>
                <option value="medical">Medical</option>
            </select>
            <textarea name="body" required placeholder="Note…" rows="3"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save note</button>
        </form>
    </x-card>

    <x-card title="Log incident">
        <form method="POST" action="{{ route('school.students.incident', $student) }}" class="space-y-2 text-sm">
            @csrf
            <input name="title" required placeholder="Incident title"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="description" placeholder="Description (optional)" rows="2"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <select name="severity"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="info">Info</option>
                <option value="warn">Warn</option>
                <option value="critical">Critical</option>
            </select>
            <button class="inline-flex items-center justify-center bg-rose-600 text-white rounded-md px-3 py-1.5 font-medium hover:bg-rose-700 active:translate-y-[1px] transition">Log incident</button>
        </form>
    </x-card>
</div>

{{-- History bento — notes / leaves / incidents --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
    <x-card title="Notes" subtitle="{{ $student->notes->count() }} on record">
        @if ($student->notes->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($student->notes as $n)
                    <li class="px-1 py-2">
                        <div class="text-[10px] uppercase tracking-wider text-zinc-400 font-semibold">{{ $n->category }}</div>
                        <div class="text-sm text-zinc-800 mt-0.5">{{ $n->body }}</div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No notes yet.</div>
        @endif
    </x-card>

    <x-card title="Leave history" subtitle="{{ $student->leaveRequests->count() }} requests">
        @if ($student->leaveRequests->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($student->leaveRequests as $l)
                    <li class="px-1 py-2 flex items-center gap-2">
                        <span class="text-[11px] text-zinc-500 font-mono tabular-nums flex-1">
                            {{ $l->from_date instanceof \Carbon\Carbon ? $l->from_date->format('M j') : $l->from_date }}
                            <span class="text-zinc-300">→</span>
                            {{ $l->to_date instanceof \Carbon\Carbon ? $l->to_date->format('M j') : $l->to_date }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-semibold">{{ $l->status }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No leave requests.</div>
        @endif
    </x-card>

    <x-card title="Incidents" subtitle="{{ $student->events->count() }} logged">
        @if ($student->events->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($student->events as $e)
                    <li class="px-1 py-2 flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-zinc-900 truncate">{{ $e->title }}</div>
                            <div class="text-[10px] uppercase tracking-wider text-zinc-500 font-semibold mt-0.5">{{ $e->severity }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No incidents logged.</div>
        @endif
    </x-card>
</div>

@endsection
