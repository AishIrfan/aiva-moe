@extends('layouts.shell')
@section('title', 'Assistance programs')
@section('subtitle', 'financial-aid programs · applications')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Trainees / Assistance</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 applications <span class="text-zinc-400">in flight.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
    <x-card title="Create program" subtitle="define a new financial-aid program">
        <form method="POST" action="{{ route('ipg.assistance.programs.store') }}" class="space-y-2 text-sm">
            @csrf
            <input name="name" required placeholder="Program name (e.g. Bantuan Pelatih)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <div class="grid grid-cols-2 gap-2">
                <input name="code" placeholder="Code"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono text-xs focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
                <input name="amount" type="number" step="0.01" required placeholder="Amount (RM)"
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Create</button>
        </form>
    </x-card>

    <x-card title="Submit application" subtitle="apply on behalf of a trainee">
        <form method="POST" action="{{ route('ipg.assistance.apply') }}" class="space-y-2 text-sm">
            @csrf
            <select name="trainee_id" required class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                @foreach ($trainees as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
            </select>
            <input name="requested_amount" type="number" step="0.01" placeholder="Requested amount (RM)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="notes" rows="2" placeholder="Justification…"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit</button>
        </form>
    </x-card>
</div>

<x-card title="Applications" subtitle="newest first">
    <div class="text-xs text-zinc-500 py-6 text-center">No applications submitted yet. Live queue lands with the IPG assistance pipeline (Phase 4).</div>
</x-card>

@endsection
