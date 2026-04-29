@extends('layouts.shell')
@section('title', 'Attendance follow-up')
@section('subtitle', 'absences & lates · last 7 days')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Attendance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Nothing <span class="text-zinc-400">to follow up.</span>
        </h1>
    </div>
</div>

@include('ipg.partials.attendance-nav')

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 mb-3">
        <svg class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
    </div>
    <div class="text-sm font-medium text-zinc-900">All clear</div>
    <div class="text-xs text-zinc-500 mt-1">When the marking flow lands, absences and lates from the past 7 days appear here.</div>
</div>

@endsection
