@extends('layouts.shell')
@section('title', 'Events management')
@section('subtitle', 'campus activities, ceremonies, gatherings')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Events</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 events <span class="text-zinc-400">on the calendar.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
    <x-card class="lg:col-span-4" title="Create event" subtitle="add a campus activity">
        <form method="POST" action="{{ route('ipg.events-management.store') }}" class="space-y-2 text-sm">
            @csrf
            <input name="title" required placeholder="Title (e.g. Konvokesyen)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <input name="location" placeholder="Location"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <div class="grid grid-cols-2 gap-2">
                <input type="datetime-local" name="starts_at" required
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 font-mono text-xs tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                <input type="datetime-local" name="ends_at" required
                       class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 font-mono text-xs tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Create</button>
        </form>
    </x-card>
    <div class="lg:col-span-8">
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="text-sm font-medium text-zinc-900">No events scheduled</div>
            <div class="text-xs text-zinc-500 mt-1">Create one on the left. The IPG events table is wired in Phase 4.</div>
        </div>
    </div>
</div>

@endsection
