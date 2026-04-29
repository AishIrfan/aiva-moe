@extends('layouts.shell')
@section('title', 'Students roster')
@section('subtitle', $school->name . ' · ' . $students->total() . ' enrolled')

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Students</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ number_format($students->total()) }} students
            <span class="text-zinc-400">on the roster.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">
        showing {{ $students->count() }} on this page
    </div>
</div>

{{-- IPG cross-mode projection: trainees on practicum at this host school (§6.1) --}}
@if (! empty($placedTrainees) && $placedTrainees->count() > 0)
    <div class="mb-5 bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-zinc-100 bg-emerald-50/40">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            </span>
            <div class="text-[10px] uppercase tracking-[0.18em] font-semibold text-emerald-700">IPG trainees · on practicum here</div>
            <span class="text-[10px] text-zinc-400 font-mono tabular-nums ml-auto">{{ $placedTrainees->count() }} active</span>
        </div>
        <div class="divide-y divide-zinc-100">
            @foreach ($placedTrainees as $p)
                @php $initial = strtoupper(mb_substr($p->trainee?->name ?? '?', 0, 1)); @endphp
                <div class="px-4 py-3 flex items-center gap-3">
                    <span class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-semibold shrink-0 ring-1 ring-emerald-200">{{ $initial }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-zinc-900">{{ $p->trainee?->name }}</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-emerald-700 bg-emerald-50 border-emerald-200">trainee · IPG</span>
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex flex-wrap items-center gap-x-3">
                            <span>{{ $p->trainee?->trainee_number }}</span>
                            <span class="text-zinc-300">·</span>
                            <span class="normal-case">{{ $p->trainee?->cohort?->display_name }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $p->start_date->format('M j') }} → {{ $p->end_date->format('M j') }}</span>
                        </div>
                    </div>
                    @if ($p->supervisor)
                        <div class="text-right shrink-0 text-[11px]">
                            <div class="text-[10px] uppercase tracking-wider text-zinc-400">Supervisor</div>
                            <div class="text-zinc-900">{{ $p->supervisor->name }}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Search --}}
<form class="flex flex-wrap items-center gap-2 mb-5 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="relative flex-1 min-w-[260px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input name="q" value="{{ request('q') }}" placeholder="Search by name, student #, or IC…"
               class="w-full bg-zinc-50 border border-zinc-200 rounded-lg pl-9 pr-3 py-1.5 text-sm
                      focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Search
    </button>
    @if (request('q'))
        <a href="{{ route('school.students') }}" class="text-xs text-zinc-500 hover:text-zinc-900 underline underline-offset-4">clear</a>
    @endif
</form>

{{-- Roster list --}}
@if ($students->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($students as $s)
            @php
                $initial = strtoupper(mb_substr($s->name, 0, 1));
                $cls = $s->activeEnrollment?->schoolClass;
                $grade = $cls?->grade?->name;
            @endphp
            <a href="{{ route('school.student-360', ['student_id' => $s->id]) }}"
               class="px-4 py-3 flex items-center gap-3 hover:bg-zinc-50 transition group">

                {{-- Avatar --}}
                <span class="w-9 h-9 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-sm font-semibold shrink-0">
                    {{ $initial }}
                </span>

                {{-- Identity --}}
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $s->name }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span>{{ $s->student_number }}</span>
                        @if ($s->ic_number)
                            <span class="text-zinc-300">·</span>
                            <span>IC {{ $s->ic_number }}</span>
                        @endif
                    </div>
                </div>

                {{-- Class chip --}}
                <div class="hidden sm:flex flex-col items-end shrink-0">
                    @if ($cls)
                        <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-zinc-700 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-0.5">
                            {{ $cls->name }}
                        </span>
                        @if ($grade)
                            <span class="text-[10px] text-zinc-400 mt-0.5">{{ $grade }}</span>
                        @endif
                    @else
                        <span class="text-[10px] text-zinc-400">unenrolled</span>
                    @endif
                </div>

                {{-- Open arrow --}}
                <span class="text-zinc-300 group-hover:text-emerald-600 transition shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </a>
        @endforeach
    </div>

    <div class="mt-5">{{ $students->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="8" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">
            @if (request('q'))
                No students match "{{ request('q') }}"
            @else
                No students on the roster yet
            @endif
        </div>
        <div class="text-xs text-zinc-500 mt-1">
            @if (request('q'))
                Try a different name, student #, or IC.
            @else
                <a href="{{ route('school.enrollment') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Open enrollment →</a>
            @endif
        </div>
    </div>
@endif

@endsection
