@extends('layouts.shell')
@section('title', 'Search')
@section('subtitle', $q ? 'results for "' . $q . '"' : 'find anything')

@php
    $sevDot = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'high'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
        'low'      => 'bg-emerald-500',
    ];
    $hasResults = $students->count() > 0 || $events->count() > 0;
@endphp

@section('content')

{{-- Heading --}}
<div class="mb-5">
    <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Search</div>
    <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
        @if ($q)
            <span class="font-mono tabular-nums text-zinc-400">{{ $students->count() + $events->count() }}</span>
            results
            <span class="text-zinc-400">for "{{ $q }}".</span>
        @else
            What are you <span class="text-zinc-400">looking for?</span>
        @endif
    </h1>
</div>

{{-- Search form --}}
<form class="mb-5 p-2 rounded-xl bg-white border border-zinc-200 shadow-card flex items-center gap-2">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input name="q" value="{{ $q }}" autofocus
               placeholder="Search students, events, schools…"
               class="w-full bg-zinc-50 border border-zinc-200 rounded-lg pl-9 pr-3 py-1.5 text-sm
                      focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
    </div>
    <button class="inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Search
    </button>
</form>

@if (! $q)
    {{-- Hint state --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">Type at least 2 characters</div>
        <div class="text-xs text-zinc-500 mt-1">We'll search across student names, IDs, and event titles.</div>
    </div>
@elseif (! $hasResults)
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">Nothing matches "{{ $q }}"</div>
        <div class="text-xs text-zinc-500 mt-1">Try a different name or shorter query.</div>
    </div>
@else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- Students --}}
        <x-card title="Students" subtitle="{{ $students->count() }} match · top 20">
            @if ($students->count() > 0)
                <ul class="divide-y divide-zinc-100 -mx-1">
                    @foreach ($students as $s)
                        @php $initial = strtoupper(mb_substr($s->name, 0, 1)); @endphp
                        <li>
                            <a href="{{ route('school.student-360', ['student_id' => $s->id]) }}"
                               class="group flex items-center gap-3 px-1 py-2 hover:bg-zinc-50 rounded-md transition">
                                <span class="w-7 h-7 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-[11px] font-semibold shrink-0">
                                    {{ $initial }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $s->name }}</div>
                                    <div class="text-[11px] text-zinc-500 font-mono tabular-nums">{{ $s->student_number }}</div>
                                </div>
                                <span class="text-zinc-300 group-hover:text-emerald-600 transition shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-xs text-zinc-500 py-4 text-center">No students match.</div>
            @endif
        </x-card>

        {{-- Events --}}
        <x-card title="Events" subtitle="{{ $events->count() }} match · most recent">
            @if ($events->count() > 0)
                <ul class="divide-y divide-zinc-100 -mx-1">
                    @foreach ($events as $e)
                        <li class="px-1 py-2 flex items-start gap-2.5">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-zinc-900 truncate">{{ $e->title }}</div>
                                <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-1.5">
                                    <span class="uppercase tracking-wide">{{ $e->type }}</span>
                                    <span class="text-zinc-300">·</span>
                                    <span>{{ $e->severity }}</span>
                                    <span class="text-zinc-300">·</span>
                                    <span>{{ $e->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="text-xs text-zinc-500 py-4 text-center">No events match.</div>
            @endif
        </x-card>
    </div>
@endif

@endsection
