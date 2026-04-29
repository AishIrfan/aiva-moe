@extends('layouts.shell')
@section('title', 'Warning letters')
@section('subtitle', 'trainees crossing the absence threshold')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            None flagged <span class="text-zinc-400">for follow-up.</span>
        </h1>
    </div>
</div>

@include('ipg.partials.attendance-nav')

<form class="flex flex-wrap items-center gap-2 mb-3 p-2 rounded-xl bg-white border border-zinc-200 shadow-card">
    <div class="flex items-center gap-2 text-xs">
        <label class="text-zinc-500">Threshold</label>
        <input type="number" name="threshold" value="5" min="1"
               class="w-20 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 text-sm font-mono tabular-nums text-center focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <span class="text-zinc-400">incidents · last 30 days</span>
    </div>
    <button class="ml-auto inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
        Recalculate
    </button>
</form>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 mb-3">
        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <div class="text-sm font-medium text-zinc-900">No trainees flagged</div>
    <div class="text-xs text-zinc-500 mt-1">Once attendance starts flowing, trainees crossing the threshold queue up here for warning letters.</div>
</div>

@endsection
