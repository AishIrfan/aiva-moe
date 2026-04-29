@extends('layouts.shell')
@section('title', 'Reports')
@section('subtitle', 'periodic exports & generated documents')

@php
    $typePill = [
        'attendance' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'incident'   => 'text-rose-700 bg-rose-50 border-rose-200',
        'analytics'  => 'text-sky-700 bg-sky-50 border-sky-200',
        'custom'     => 'text-zinc-700 bg-zinc-50 border-zinc-200',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Reports</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $reports->total() }} reports
            <span class="text-zinc-400">in the archive.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="Generate report" subtitle="picks data for the chosen period">
    <form method="POST" action="{{ route('school.reports.store') }}"
          class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm items-center">
        @csrf
        <select name="type" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="attendance">Attendance</option>
            <option value="incident">Incident</option>
            <option value="analytics">Analytics</option>
            <option value="custom">Custom</option>
        </select>
        <input name="title" placeholder="Title" required
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="period" value="{{ now()->format('Y-m') }}" placeholder="YYYY-MM"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Generate</button>
    </form>
</x-card>

@if ($reports->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($reports as $r)
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-9 h-9 rounded-md bg-zinc-100 text-zinc-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/><path d="M7 21V11M12 21V5M17 21v-7"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $r->title }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                        period {{ $r->period }} · created {{ $r->created_at?->diffForHumans() }}
                    </div>
                </div>
                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $typePill[$r->type] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $r->type }}
                </span>
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $reports->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No reports yet</div>
        <div class="text-xs text-zinc-500 mt-1">Generate one above to populate the archive.</div>
    </div>
@endif

@endsection
