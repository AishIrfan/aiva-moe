@extends('layouts.shell')
@section('title', 'Assistance programs')
@section('subtitle', 'create programs · process applications')

@php
    $statusPill = [
        'submitted' => 'text-sky-700 bg-sky-50 border-sky-200',
        'verified'  => 'text-amber-700 bg-amber-50 border-amber-200',
        'approved'  => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'disbursed' => 'text-zinc-700 bg-zinc-50 border-zinc-200',
        'rejected'  => 'text-rose-700 bg-rose-50 border-rose-200',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Assistance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $applications->total() }} applications
            <span class="text-zinc-400">in flight.</span>
        </h1>
    </div>
    <a href="{{ route('school.assistance.export') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-zinc-700 text-sm hover:border-zinc-300 transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
        </svg>
        Export CSV
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
    <x-card title="Create program" subtitle="define a new financial-aid program">
        <form method="POST" action="{{ route('school.assistance.programs.store') }}" class="space-y-2 text-sm">
            @csrf
            <input name="name" required placeholder="Program name (e.g. Bantuan Awal Persekolahan)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <div class="grid grid-cols-2 gap-2">
                <input name="code" placeholder="Code (e.g. BAP-2026)"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono text-xs focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
                <input name="amount" type="number" step="0.01" required placeholder="Amount (RM)"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
            <textarea name="description" rows="2" placeholder="Eligibility criteria, notes…"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Create</button>
        </form>
    </x-card>

    <x-card title="Submit application" subtitle="apply on behalf of a student">
        <form method="POST" action="{{ route('school.assistance.apply') }}" class="space-y-2 text-sm">
            @csrf
            <select name="assistance_program_id" required
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                @foreach ($programs as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} (RM{{ number_format($p->amount, 2) }})</option>
                @endforeach
            </select>
            <select name="student_id" required
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                @foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
            <input name="requested_amount" type="number" step="0.01" placeholder="Requested amount (RM)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="notes" rows="2" placeholder="Justification, notes…"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
        </form>
    </x-card>
</div>

<x-card title="Applications" subtitle="newest first">
    @if ($applications->count() > 0)
        <div class="-mx-5 -mb-5 border-t border-zinc-100 divide-y divide-zinc-100">
            @foreach ($applications as $a)
                <div class="px-5 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium text-zinc-900 truncate">{{ $a->student?->name }}</div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 truncate">{{ $a->program?->name }}</div>
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-mono tabular-nums shrink-0">
                        <div class="text-right">
                            <div class="text-[9px] uppercase tracking-wider text-zinc-400">Requested</div>
                            <div class="text-zinc-700">RM {{ number_format((float) $a->requested_amount, 2) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[9px] uppercase tracking-wider text-zinc-400">Approved</div>
                            <div class="text-emerald-700">{{ $a->approved_amount ? 'RM ' . number_format((float) $a->approved_amount, 2) : '—' }}</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$a->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                        {{ $a->status }}
                    </span>
                    <div class="flex items-center gap-1 shrink-0">
                        @if ($a->status === 'submitted')
                            <form method="POST" action="{{ route('school.assistance.verify', $a) }}">
                                @csrf
                                <button class="text-xs font-medium text-zinc-700 hover:text-zinc-900 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-md px-2.5 py-1 transition">Verify</button>
                            </form>
                        @endif
                        @if ($a->status === 'verified')
                            <form method="POST" action="{{ route('school.assistance.decide', $a) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approved"/>
                                <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">Approve</button>
                            </form>
                        @endif
                        @if ($a->status === 'approved')
                            <form method="POST" action="{{ route('school.assistance.disburse', $a) }}">
                                @csrf
                                <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">Disburse</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-5">{{ $applications->links() }}</div>
    @else
        <div class="text-xs text-zinc-500 py-4 text-center">No applications submitted yet.</div>
    @endif
</x-card>

@endsection
