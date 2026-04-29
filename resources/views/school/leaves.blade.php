@extends('layouts.shell')
@section('title', 'Leave requests')
@section('subtitle', 'submit, approve, or reject student leaves')

@php
    $statusPill = [
        'pending'  => 'text-amber-700 bg-amber-50 border-amber-200',
        'approved' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'rejected' => 'text-rose-700 bg-rose-50 border-rose-200',
    ];

    $byStatus = $leaves->getCollection()->groupBy('status')->map->count();
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Leaves</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $leaves->total() }} requests
            <span class="text-zinc-400">on the queue.</span>
        </h1>
    </div>
</div>

{{-- Status strip --}}
<div class="grid grid-cols-3 gap-2 mb-3">
    <div class="rounded-xl border border-amber-100 bg-amber-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-amber-700/70 mb-1">Pending</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-amber-700">{{ $byStatus['pending'] ?? 0 }}</div>
    </div>
    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-emerald-700/70 mb-1">Approved</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-emerald-700">{{ $byStatus['approved'] ?? 0 }}</div>
    </div>
    <div class="rounded-xl border border-rose-100 bg-rose-50/60 p-3">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-rose-700/70 mb-1">Rejected</div>
        <div class="font-mono tabular-nums text-2xl font-semibold text-rose-700">{{ $byStatus['rejected'] ?? 0 }}</div>
    </div>
</div>

{{-- Submit leave --}}
<x-card class="mb-3" title="Submit leave" subtitle="creates a pending request">
    <form method="POST" action="{{ route('school.leaves.store') }}" class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">
        @csrf
        <select name="student_id" required
                class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            @foreach ($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <input type="date" name="from_date" required
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <input type="date" name="to_date" required
               class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <select name="type"
                class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="personal">Personal</option>
            <option value="medical">Medical</option>
            <option value="family">Family</option>
        </select>
        <input name="reason" placeholder="Reason"
               class="md:col-span-1 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
    </form>
</x-card>

@if ($leaves->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($leaves as $l)
            @php $initial = strtoupper(mb_substr($l->student?->name ?? '?', 0, 1)); @endphp
            <div class="px-4 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                <span class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ $initial }}</span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $l->student?->name ?? '—' }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                        <span>{{ $l->from_date instanceof \Carbon\Carbon ? $l->from_date->format('M j') : $l->from_date }}</span>
                        <span class="text-zinc-300">→</span>
                        <span>{{ $l->to_date instanceof \Carbon\Carbon ? $l->to_date->format('M j') : $l->to_date }}</span>
                        <span class="text-zinc-300">·</span>
                        <span class="uppercase tracking-wide normal-case">{{ $l->type }}</span>
                    </div>
                </div>

                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$l->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                    {{ $l->status }}
                </span>

                @if ($l->status === 'pending')
                    <div class="flex items-center gap-1 shrink-0">
                        <form method="POST" action="{{ route('school.leaves.decide', $l) }}">
                            @csrf
                            <input type="hidden" name="decision" value="approved"/>
                            <button class="text-xs font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('school.leaves.decide', $l) }}">
                            @csrf
                            <input type="hidden" name="decision" value="rejected"/>
                            <button class="text-xs font-medium text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md px-2.5 py-1 transition">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $leaves->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No leave requests yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use the form above to submit one.</div>
    </div>
@endif

@endsection
