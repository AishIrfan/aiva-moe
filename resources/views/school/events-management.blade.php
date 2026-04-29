@extends('layouts.shell')
@section('title', 'Events management')
@section('subtitle', 'school activities, ceremonies, and gatherings')

@php
    $statusPill = [
        'draft'     => 'text-zinc-700 bg-zinc-50 border-zinc-200',
        'scheduled' => 'text-sky-700 bg-sky-50 border-sky-200',
        'live'      => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'completed' => 'text-zinc-600 bg-zinc-50 border-zinc-200',
        'cancelled' => 'text-rose-700 bg-rose-50 border-rose-200',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Events</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $events->total() }} events
            <span class="text-zinc-400">on the calendar.</span>
        </h1>
    </div>
    <a href="{{ route('school.events-management.export') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-zinc-700 text-sm hover:border-zinc-300 transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
        </svg>
        Export CSV
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

    <x-card class="lg:col-span-4" title="Create event" subtitle="add a school activity">
        <form method="POST" action="{{ route('school.events-management.store') }}" class="space-y-2 text-sm">
            @csrf
            <input name="title" required placeholder="Title (e.g. Sports day, PTA meeting)"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="description" rows="2" placeholder="Description"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <input name="location" placeholder="Location"
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-0.5">Starts</label>
                    <input type="datetime-local" name="starts_at" required
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 font-mono text-xs tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-0.5">Ends</label>
                    <input type="datetime-local" name="ends_at" required
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 font-mono text-xs tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Create</button>
        </form>
    </x-card>

    <div class="lg:col-span-8">
        @if ($events->count() > 0)
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
                @foreach ($events as $e)
                    <div class="px-4 py-3 flex items-center gap-3 flex-wrap md:flex-nowrap">
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-zinc-900 truncate">{{ $e->title }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                                {{ $e->starts_at?->format('D · M j, H:i') }}
                                @if ($e->location)
                                    <span class="text-zinc-300">·</span>
                                    <span class="normal-case">{{ $e->location }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border shrink-0 {{ $statusPill[$e->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                            {{ $e->status }}
                        </span>
                        <form method="POST" action="{{ route('school.events-management.transition', $e) }}" class="flex items-center gap-1 text-xs shrink-0">
                            @csrf
                            <select name="status"
                                    class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1 focus:outline-none focus:bg-white focus:border-zinc-300">
                                @foreach (\App\Services\EventManagementService::STATES as $s)
                                    <option value="{{ $s }}" @selected($e->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button class="font-medium text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md px-2.5 py-1 transition">
                                Update
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            <div class="mt-5">{{ $events->links() }}</div>
        @else
            <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
                <div class="text-sm font-medium text-zinc-900">No events scheduled</div>
                <div class="text-xs text-zinc-500 mt-1">Use the form on the left to create your first.</div>
            </div>
        @endif
    </div>
</div>

@endsection
