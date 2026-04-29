@extends('layouts.shell')
@section('title', 'Laporan disiplin')
@section('subtitle', 'discipline cases · investigation queue')

@php
    $sevPill = [
        'low'    => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'medium' => 'text-amber-700 bg-amber-50 border-amber-200',
        'high'   => 'text-rose-700 bg-rose-50 border-rose-200',
    ];

    $statusPill = [
        'submitted'           => 'text-sky-700 bg-sky-50 border-sky-200',
        'pending_review'      => 'text-amber-700 bg-amber-50 border-amber-200',
        'under_investigation' => 'text-amber-800 bg-amber-100 border-amber-300',
        'action_required'     => 'text-rose-700 bg-rose-50 border-rose-200',
        'resolved'            => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'closed'              => 'text-zinc-700 bg-zinc-50 border-zinc-200',
        'rejected'            => 'text-zinc-600 bg-zinc-50 border-zinc-200',
        'cancelled'           => 'text-zinc-600 bg-zinc-50 border-zinc-200',
    ];

    $tabs = ['list','new','pending','reports'];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Discipline</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Discipline cases <span class="text-zinc-400">to act on.</span>
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
    <x-card title="New case" subtitle="lodge a discipline incident">
        <form method="POST" action="{{ route('school.laporan-masalah-disiplin.store') }}" class="space-y-2 text-sm">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
                <input type="number" name="student_id" required placeholder="Student ID"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
                <select name="category"
                        class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                    <option value="bullying">Bullying</option>
                    <option value="absenteeism">Absenteeism</option>
                    <option value="misconduct">Misconduct</option>
                    <option value="uniform">Uniform</option>
                    <option value="other">Other</option>
                </select>
                <select name="severity"
                        class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
                <input type="date" name="incident_date" required
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                <input name="location" placeholder="Location"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
            <textarea name="description" rows="3" placeholder="Description of the incident…"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit case</button>
        </form>
    </x-card>
@else
    @if ($cases->count() > 0)
        <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
            @foreach ($cases as $c)
                @php $initial = strtoupper(mb_substr($c->student?->name ?? '?', 0, 1)); @endphp
                <div class="px-4 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                    <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-zinc-900 truncate">{{ $c->student?->name ?? '—' }}</span>
                            <span class="text-[10px] font-mono tabular-nums text-zinc-400">{{ $c->case_number }}</span>
                            <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $sevPill[$c->severity] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                                {{ $c->severity }}
                            </span>
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 uppercase tracking-wide font-semibold">{{ $c->category }}</div>
                    </div>
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$c->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                        {{ str_replace('_', ' ', $c->status) }}
                    </span>
                    <form method="POST" action="{{ route('school.laporan-masalah-disiplin.transition', $c) }}" class="flex items-center gap-1 text-xs shrink-0">
                        @csrf
                        <select name="status" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 focus:outline-none focus:bg-white focus:border-zinc-300">
                            @foreach (['submitted','pending_review','under_investigation','action_required','resolved','closed','rejected','cancelled'] as $st)
                                <option value="{{ $st }}" @selected($c->status === $st)>{{ str_replace('_', ' ', $st) }}</option>
                            @endforeach
                        </select>
                        <button class="font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">Apply</button>
                    </form>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $cases->links() }}</div>
    @else
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="text-sm font-medium text-zinc-900">No cases in this view</div>
            <div class="text-xs text-zinc-500 mt-1">Switch tabs or create a new case.</div>
        </div>
    @endif
@endif

@endsection
