@extends('layouts.shell')
@section('title', 'Leave requests')
@section('subtitle', 'trainee leaves · approval queue')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Trainees / Leaves</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 requests <span class="text-zinc-400">on the queue.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-3 gap-2 mb-3">
    @foreach (['Pending', 'Approved', 'Rejected'] as $idx => $label)
        @php $tone = ['amber','emerald','rose'][$idx]; @endphp
        <div class="rounded-xl border border-{{ $tone }}-100 bg-{{ $tone }}-50/60 p-3">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-{{ $tone }}-700/70 mb-1">{{ $label }}</div>
            <div class="font-mono tabular-nums text-2xl font-semibold text-{{ $tone }}-700">0</div>
        </div>
    @endforeach
</div>

<x-card class="mb-3" title="Submit leave" subtitle="creates a pending request">
    <form method="POST" action="{{ route('ipg.leaves.store') }}" class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">
        @csrf
        <select name="trainee_id" required class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            @foreach ($trainees as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from_date" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <input type="date" name="to_date" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <select name="type" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="personal">Personal</option><option value="medical">Medical</option><option value="family">Family</option>
        </select>
        <input name="reason" placeholder="Reason" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
    </form>
</x-card>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No leave requests yet</div>
    <div class="text-xs text-zinc-500 mt-1">Submit one above. The IPG-side leaves table is wired in Phase 4.</div>
</div>

@endsection
