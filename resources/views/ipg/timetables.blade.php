@extends('layouts.shell')
@section('title', 'Timetables')
@section('subtitle', 'semester course schedules · view-only')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics / Timetables</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Filter by <span class="text-zinc-400">cohort or pensyarah.</span>
        </h1>
    </div>
</div>

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <select name="cohort_id" class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All cohorts</option>
        @foreach ($cohorts as $c)
            <option value="{{ $c->id }}">{{ $c->display_name }}</option>
        @endforeach
    </select>
    <select name="pensyarah_id" class="bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
        <option value="">All pensyarah</option>
        @foreach ($pensyarahs as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Filter
    </button>
</form>

<div class="bg-white border border-zinc-200 rounded-xl shadow-card p-10 text-center">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
        <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>
        </svg>
    </div>
    <div class="text-sm font-medium text-zinc-900">No timetable rows yet</div>
    <div class="text-xs text-zinc-500 mt-1">Per-period schedule entries appear here once <code class="font-mono text-[10px] bg-zinc-100 rounded px-1">timetables</code> is wired (semester-aware).</div>
</div>

@endsection
