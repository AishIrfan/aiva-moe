@extends('layouts.shell')
@section('title', 'Reports')
@section('subtitle', 'periodic exports · generated documents')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Reports</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 reports <span class="text-zinc-400">in the archive.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="Generate report" subtitle="picks data for the chosen period">
    <form method="POST" action="{{ route('ipg.reports.store') }}"
          class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm items-center">
        @csrf
        <select name="type" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="attendance">Attendance</option>
            <option value="discipline">Discipline</option>
            <option value="practicum">Practicum</option>
            <option value="cohort">Cohort</option>
            <option value="custom">Custom</option>
        </select>
        <input name="title" placeholder="Title" required
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="period" value="{{ now()->format('Y-m') }}" placeholder="YYYY-MM"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Generate</button>
    </form>
</x-card>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No reports yet</div>
    <div class="text-xs text-zinc-500 mt-1">Generated exports land here once the report pipeline is wired (Phase 4).</div>
</div>

@endsection
