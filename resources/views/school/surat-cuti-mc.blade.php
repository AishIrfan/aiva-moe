@extends('layouts.shell')
@section('title', 'Surat Cuti / MC')
@section('subtitle', 'leave & medical certificate submissions')

@php
    $statusPill = [
        'submitted'             => 'text-sky-700 bg-sky-50 border-sky-200',
        'pending_review'        => 'text-amber-700 bg-amber-50 border-amber-200',
        'approved'              => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'rejected'              => 'text-rose-700 bg-rose-50 border-rose-200',
        'cancelled'             => 'text-zinc-600 bg-zinc-50 border-zinc-200',
        'returned_for_revision' => 'text-amber-700 bg-amber-50 border-amber-200',
    ];

    $tabs = ['list','new','pending','history'];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Cuti & MC</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Submissions <span class="text-zinc-400">queue.</span>
        </h1>
    </div>
</div>

<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($tabs as $t)
        <a href="?tab={{ $t }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ $tab === $t ? 'border-zinc-900 bg-zinc-900 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">
            {{ ucfirst($t) }}
        </a>
    @endforeach
</nav>

@if ($tab === 'new')
    <x-card title="New submission" subtitle="lodge a leave or MC request">
        <form method="POST" action="{{ route('school.surat-cuti-mc.store') }}"
              class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm items-center">
            @csrf
            <input type="number" name="student_id" required placeholder="Student ID"
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <select name="category"
                    class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="cuti">Cuti</option>
                <option value="mc">MC</option>
            </select>
            <input type="date" name="from_date" required
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            <input type="date" name="to_date" required
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            <input name="reason" placeholder="Reason"
                   class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
        </form>
    </x-card>
@else
    @if ($submissions->count() > 0)
        <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
            @foreach ($submissions as $s)
                @php $initial = strtoupper(mb_substr($s->student?->name ?? '?', 0, 1)); @endphp
                <div class="px-4 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                    <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-zinc-900 truncate">{{ $s->student?->name ?? '—' }}</div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2 flex-wrap">
                            <span class="uppercase tracking-wide font-semibold">{{ $s->category }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $s->from_date instanceof \Carbon\Carbon ? $s->from_date->format('M j') : $s->from_date }}</span>
                            <span class="text-zinc-300">→</span>
                            <span>{{ $s->to_date instanceof \Carbon\Carbon ? $s->to_date->format('M j') : $s->to_date }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $s->day_count }}d</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$s->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                        {{ str_replace('_', ' ', $s->status) }}
                    </span>
                    <form method="POST" action="{{ route('school.surat-cuti-mc.transition', $s) }}" class="flex items-center gap-1 text-xs shrink-0">
                        @csrf
                        <select name="status" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 focus:outline-none focus:bg-white focus:border-zinc-300">
                            @foreach (['submitted','pending_review','approved','rejected','cancelled','returned_for_revision'] as $st)
                                <option value="{{ $st }}" @selected($s->status === $st)>{{ str_replace('_', ' ', $st) }}</option>
                            @endforeach
                        </select>
                        <button class="font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">Apply</button>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $submissions->links() }}</div>
    @else
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="text-sm font-medium text-zinc-900">No submissions in this view</div>
            <div class="text-xs text-zinc-500 mt-1">Use the "New" tab to lodge one.</div>
        </div>
    @endif
@endif

@endsection
