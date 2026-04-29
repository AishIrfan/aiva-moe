@extends('layouts.shell')
@section('title', 'Cameras & zones')
@section('subtitle', $school->name)

@php
    $online   = $cameras->where('online', true)->count();
    $offline  = $cameras->count() - $online;
    $total    = $cameras->count();
    $onlinePct = $total > 0 ? round(($online / $total) * 100, 1) : 0;
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Cameras</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $online }}<span class="text-zinc-400">/{{ $total }}</span> live
            <span class="text-zinc-400">on the fleet.</span>
        </h1>
    </div>

    <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-emerald-700 bg-emerald-50 border border-emerald-200/70 font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            {{ $online }} online
        </span>
        @if ($offline > 0)
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-rose-700 bg-rose-50 border border-rose-200/70 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                {{ $offline }} offline
            </span>
        @endif
    </div>
</div>

{{-- Uptime gauge --}}
@if ($total > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-4 mb-3">
        <div class="flex items-center justify-between text-xs mb-2">
            <span class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400">Fleet uptime</span>
            <span class="font-mono tabular-nums text-zinc-700">{{ $onlinePct }}%</span>
        </div>
        <div class="shimmer-bar h-1.5">
            <div class="absolute inset-y-0 left-0 rounded-full {{ $onlinePct >= 95 ? 'bg-emerald-500' : ($onlinePct >= 80 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $onlinePct }}%"></div>
        </div>
    </div>
@endif

{{-- Add camera --}}
<x-card class="mb-3" title="Add camera" subtitle="register a new feed for this school">
    <form method="POST" action="{{ route('school.cameras.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-2">
        @csrf
        <input name="name" placeholder="Name" required
               class="md:col-span-3 bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
        <input name="serial" placeholder="Serial"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
        <input name="stream_url" placeholder="rtsp://… or https://…"
               class="md:col-span-4 bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400 font-mono"/>
        <select name="zone_id"
                class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:bg-white focus:border-zinc-300 focus:outline-none transition">
            <option value="">No zone</option>
            @foreach ($zones as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
            @endforeach
        </select>
        <button class="md:col-span-1 inline-flex items-center justify-center bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Add</button>
    </form>
</x-card>

{{-- Camera grid --}}
@if ($cameras->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($cameras as $cam)
            <div class="group bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden hover:border-zinc-300 transition">

                {{-- Pseudo-feed preview --}}
                <div class="relative aspect-video bg-zinc-900 grid place-items-center overflow-hidden scan-drift">
                    {{-- Subtle scanline pattern --}}
                    <div class="absolute inset-0 opacity-30" style="background-image: linear-gradient(to bottom, transparent 0, transparent 2px, rgba(255,255,255,0.04) 3px); background-size: 100% 3px;"></div>

                    @if ($cam->online)
                        <svg class="w-8 h-8 text-zinc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>
                        </svg>
                    @else
                        <svg class="w-8 h-8 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>
                            <line x1="2" y1="2" x2="22" y2="22"/>
                        </svg>
                    @endif

                    {{-- Status badge top-right --}}
                    <div class="absolute top-2 right-2">
                        @if ($cam->online)
                            <span class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium text-emerald-300 bg-emerald-950/60 border border-emerald-500/30">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                </span>
                                LIVE
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium text-rose-300 bg-rose-950/60 border border-rose-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                OFFLINE
                            </span>
                        @endif
                    </div>

                    {{-- Cam name overlay bottom-left --}}
                    <div class="absolute bottom-2 left-2 text-[10px] font-mono tracking-wide text-white/70">{{ $cam->name }}</div>
                </div>

                {{-- Body --}}
                <div class="p-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-zinc-900 truncate">{{ $cam->name }}</div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                            <span>{{ $cam->zone?->name ?? 'no zone' }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $cam->config?->retention_days ?? 30 }}d retention</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('school.cameras.toggle', $cam) }}" class="shrink-0">
                        @csrf
                        <button class="text-xs font-medium text-zinc-700 hover:text-zinc-900 bg-zinc-50 hover:bg-zinc-100 border border-zinc-200 rounded-md px-2.5 py-1 transition">
                            Toggle
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
            <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>
            </svg>
        </div>
        <div class="text-sm font-medium text-zinc-900">No cameras registered yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use the form above to register your first feed.</div>
    </div>
@endif

@endsection
