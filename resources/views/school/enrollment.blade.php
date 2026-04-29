@extends('layouts.shell')
@section('title', 'Enrollment')
@section('subtitle', 'assign or transfer students between classes')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Enrollment</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $students->total() }} students
            <span class="text-zinc-400">to place.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">page {{ $students->currentPage() }} / {{ max(1, $students->lastPage()) }}</div>
</div>

@if ($students->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($students as $s)
            @php
                $initial = strtoupper(mb_substr($s->name, 0, 1));
                $cls     = $s->activeEnrollment?->schoolClass;
                $grade   = $cls?->grade?->name;
                $hasEnrollment = (bool) $s->activeEnrollment;
            @endphp
            <div class="px-4 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>

                <div class="min-w-0 flex-1 md:min-w-[180px]">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $s->name }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">
                        @if ($cls)
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-zinc-700 bg-zinc-50 border border-zinc-200 rounded-md px-1.5 py-0.5">
                                {{ $grade ? $grade . ' · ' : '' }}{{ $cls->name }}
                            </span>
                        @else
                            <span class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-1.5 py-0.5 font-medium">unenrolled</span>
                        @endif
                    </div>
                </div>

                @if ($hasEnrollment)
                    <form method="POST" action="{{ route('school.enrollment.transfer') }}" class="flex flex-wrap items-center gap-1 text-xs">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $s->id }}"/>
                        <select name="school_class_id"
                                class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 focus:outline-none focus:bg-white focus:border-zinc-300">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->grade->name }} · {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <input name="reason" placeholder="Reason (required)" required
                               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 w-32 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
                        <button class="font-medium text-amber-700 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-md px-2.5 py-1 transition">
                            Transfer
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('school.enrollment.assign') }}" class="flex flex-wrap items-center gap-1 text-xs">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $s->id }}"/>
                        <select name="school_class_id"
                                class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 focus:outline-none focus:bg-white focus:border-zinc-300">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->grade->name }} · {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <button class="font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">
                            Assign
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-5">{{ $students->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No students to place</div>
        <div class="text-xs text-zinc-500 mt-1">Add students from the Students page first.</div>
    </div>
@endif

@endsection
