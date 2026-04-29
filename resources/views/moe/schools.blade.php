@extends('layouts.shell')
@section('title', 'Schools directory')
@section('subtitle', $schools->total() . ' registered')

@section('content')

{{-- Heading + counter --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry / Schools</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Pick a school <span class="text-zinc-400">to drill in.</span>
        </h1>
    </div>
    <div class="text-xs text-zinc-500 font-mono tabular-nums">
        showing {{ $schools->count() }} of {{ $schools->total() }}
    </div>
</div>

{{-- Filter toolbar --}}
<form class="flex flex-wrap items-center gap-2 mb-5 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="relative flex-1 min-w-[220px]">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
        </svg>
        <input name="q" value="{{ request('q') }}" placeholder="Search by name…"
               class="w-full bg-zinc-50 border border-zinc-200 rounded-lg pl-9 pr-3 py-1.5 text-sm
                      focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
    </div>

    <input name="state" value="{{ request('state') }}" placeholder="State"
           class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 w-32 text-sm
                  focus:bg-white focus:border-zinc-300 focus:outline-none transition"/>

    <select name="sort"
            class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm
                   focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="name"  @selected(request('sort') === 'name')>Sort: name</option>
        <option value="state" @selected(request('sort') === 'state')>Sort: state</option>
    </select>

    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Filter
    </button>
</form>

{{-- School grid --}}
@if ($schools->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($schools as $s)
            @php
                $inc  = (int) ($incidentCounts[$s->id] ?? 0);
                $tier = $inc === 0 ? 'calm' : ($inc < 5 ? 'low' : ($inc < 15 ? 'med' : 'hot'));
            @endphp
            <div class="group relative bg-white border border-zinc-200 rounded-xl shadow-card p-4 flex flex-col hover:border-zinc-300 transition">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-0.5">{{ $s->state ?: 'Unspecified' }}</div>
                        <div class="text-sm font-semibold text-zinc-900 leading-tight">{{ $s->name }}</div>
                    </div>

                    @if ($tier === 'calm')
                        <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-1.5 py-0.5">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>calm
                        </span>
                    @elseif ($tier === 'low')
                        <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-medium text-zinc-700 bg-zinc-50 border border-zinc-200 rounded px-1.5 py-0.5">
                            <span class="w-1 h-1 rounded-full bg-zinc-500"></span>{{ $inc }} inc
                        </span>
                    @elseif ($tier === 'med')
                        <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.5">
                            <span class="w-1 h-1 rounded-full bg-amber-500"></span>{{ $inc }} inc
                        </span>
                    @else
                        <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded px-1.5 py-0.5">
                            <span class="w-1 h-1 rounded-full bg-rose-500"></span>{{ $inc }} hot
                        </span>
                    @endif
                </div>

                <form method="POST" action="{{ route('moe.schools.select') }}" class="mt-4 flex items-center justify-between">
                    @csrf
                    <input type="hidden" name="school_id" value="{{ $s->id }}"/>
                    <span class="text-[11px] text-zinc-400 font-mono tabular-nums">id · {{ $s->id }}</span>
                    <button class="inline-flex items-center gap-1 text-xs font-medium text-zinc-700 hover:text-emerald-700 bg-zinc-50 hover:bg-emerald-50 border border-zinc-200 hover:border-emerald-200 rounded-md px-2.5 py-1 transition">
                        Open
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $schools->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">No schools match your filter</div>
        <div class="text-xs text-zinc-500 mt-1">Try clearing the search or state inputs above.</div>
    </div>
@endif

@endsection
