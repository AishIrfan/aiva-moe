@extends('layouts.shell')
@section('title', 'BPG · Campuses')
@section('subtitle', 'pick an IPG to drill in')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Ministry (BPG) / Campuses</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $campuses->count() }} {{ Str::plural('campus', $campuses->count()) }}
            <span class="text-zinc-400">on the network.</span>
        </h1>
    </div>
</div>

@if ($campuses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 stagger-in">
        @foreach ($campuses as $c)
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-4 flex flex-col hover-lift">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-0.5">{{ $c->state ?: 'Unspecified' }}</div>
                        <div class="text-sm font-semibold text-zinc-900 leading-tight">{{ $c->name }}</div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">{{ $c->code }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('bpg.campuses.select') }}" class="mt-4 flex items-center justify-between">
                    @csrf
                    <input type="hidden" name="campus_id" value="{{ $c->id }}"/>
                    <span class="text-[11px] text-zinc-400 font-mono tabular-nums">id · {{ $c->id }}</span>
                    <button class="inline-flex items-center gap-1 text-xs font-medium text-zinc-700 hover:text-emerald-700 bg-zinc-50 hover:bg-emerald-50 border border-zinc-200 hover:border-emerald-200 rounded-md px-2.5 py-1 transition">
                        Open
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No campuses registered yet</div>
        <div class="text-xs text-zinc-500 mt-1">Add one via the seeder or admin tooling.</div>
    </div>
@endif

@endsection
