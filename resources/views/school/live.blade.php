@extends('layouts.shell')
@section('title', 'Live monitor')
@section('subtitle', $school->name)

@php
    $online  = $cameras->where('online', true)->count();
    $total   = $cameras->count();

    $sevDot = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'high'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
        'low'      => 'bg-emerald-500',
    ];
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Live monitor</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $online }}<span class="text-zinc-400">/{{ $total }}</span> feeds
            <span class="text-zinc-400">streaming.</span>
        </h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5 font-mono tabular-nums">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
            </span>
            live · {{ now()->format('H:i:s') }} MYT
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

    {{-- Camera wall --}}
    <div class="lg:col-span-8">
        <div class="flex items-center justify-between mb-2">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400">Camera wall</div>
            <a href="{{ route('school.cameras') }}" class="text-[11px] text-emerald-700 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
                Manage
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        @if ($cameras->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2.5">
                @foreach ($cameras as $cam)
                    <div class="group relative bg-zinc-900 rounded-xl border border-zinc-200 overflow-hidden aspect-video shadow-card scan-drift hover-lift">
                        {{-- Subtle scanlines --}}
                        <div class="absolute inset-0 opacity-30" style="background-image: linear-gradient(to bottom, transparent 0, transparent 2px, rgba(255,255,255,0.04) 3px); background-size: 100% 3px;"></div>

                        {{-- Center icon --}}
                        <div class="absolute inset-0 grid place-items-center">
                            @if ($cam->online)
                                <svg class="w-7 h-7 text-zinc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>
                                </svg>
                            @else
                                <svg class="w-7 h-7 text-zinc-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m23 8-6 4 6 4V8Z"/><rect x="1" y="6" width="16" height="12" rx="2"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            @endif
                        </div>

                        {{-- Top-left status --}}
                        <div class="absolute top-2 left-2">
                            @if ($cam->online)
                                <span class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium font-mono tracking-wide text-emerald-300 bg-emerald-950/60 border border-emerald-500/30">
                                    <span class="relative flex h-1.5 w-1.5">
                                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    </span>
                                    LIVE
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-1.5 py-0.5 rounded text-[10px] font-medium font-mono tracking-wide text-rose-300 bg-rose-950/60 border border-rose-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                    OFFLINE
                                </span>
                            @endif
                        </div>

                        {{-- Bottom overlay: name + zone --}}
                        <div class="absolute bottom-0 inset-x-0 px-2.5 py-1.5 bg-gradient-to-t from-black/70 to-transparent">
                            <div class="text-xs font-medium text-white/90 truncate">{{ $cam->name }}</div>
                            <div class="text-[10px] text-white/50 font-mono">{{ $cam->zone?->name ?? 'no zone' }}</div>
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
                <div class="text-sm font-medium text-zinc-900">No cameras yet</div>
                <div class="text-xs text-zinc-500 mt-1">
                    <a href="{{ route('school.cameras') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Register a feed →</a>
                </div>
            </div>
        @endif
    </div>

    {{-- Recent events feed --}}
    <div class="lg:col-span-4">
        <div class="flex items-center justify-between mb-2">
            <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400">Recent events</div>
            <a href="{{ route('school.alerts') }}" class="text-[11px] text-emerald-700 hover:text-emerald-800 font-medium inline-flex items-center gap-1">
                All
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
            @forelse ($recentEvents as $e)
                <div class="px-3 py-2.5 flex items-start gap-2.5">
                    <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>
                    <div class="min-w-0 flex-1">
                        <div class="text-[13px] font-medium text-zinc-900 leading-tight truncate">{{ $e->title }}</div>
                        <div class="text-[10px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-1.5">
                            <span class="uppercase tracking-wide">{{ $e->type }}</span>
                            <span class="text-zinc-300">·</span>
                            <span>{{ $e->created_at?->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-3 py-6 text-center text-xs text-zinc-500">Nothing recent.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection
