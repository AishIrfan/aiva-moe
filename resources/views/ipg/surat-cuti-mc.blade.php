@extends('layouts.shell')
@section('title', 'Surat Cuti / MC')
@section('subtitle', 'leave & medical certificate submissions')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Cuti & MC</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Submissions <span class="text-zinc-400">queue.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="New submission" subtitle="lodge a leave or MC request">
    <form method="POST" action="{{ route('ipg.surat-cuti-mc.store') }}"
          class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm items-center">
        @csrf
        <input type="number" name="trainee_id" required placeholder="Trainee ID"
               class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <select name="category" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="cuti">Cuti</option><option value="mc">MC</option>
        </select>
        <input type="date" name="from_date" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <input type="date" name="to_date" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <input name="reason" placeholder="Reason"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
    </form>
</x-card>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No submissions in this view</div>
    <div class="text-xs text-zinc-500 mt-1">Lodge one above. Status workflow (submitted → review → approved/rejected) lands in Phase 4.</div>
</div>

@endsection
