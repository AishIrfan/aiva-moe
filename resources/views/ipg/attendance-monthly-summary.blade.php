@extends('layouts.shell')
@section('title', 'Monthly attendance summary')
@section('subtitle', now()->format('Y-m'))

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Per-trainee tally <span class="text-zinc-400">{{ now()->format('M Y') }}.</span>
        </h1>
    </div>
</div>

@include('ipg.partials.attendance-nav')

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="flex items-center gap-2 text-xs">
        <label class="text-zinc-500">Month</label>
        <input type="month" name="month" value="{{ now()->format('Y-m') }}"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Apply
    </button>
</form>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No data for {{ now()->format('M Y') }}</div>
    <div class="text-xs text-zinc-500 mt-1">Per-trainee monthly buckets (present/late/absent/leave/MC) come online with the marking flow.</div>
</div>

@endsection
