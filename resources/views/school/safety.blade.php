@extends('layouts.shell')
@section('title', 'Safety & emergency')
@section('subtitle', 'log incidents · broadcast to channels')

@php
    $sevDot = [
        'critical' => 'bg-rose-500',
        'warn'     => 'bg-amber-500',
        'info'     => 'bg-sky-500',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Safety</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Sound the alarm,
            <span class="text-zinc-400">reach the right people.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
    <x-card title="Log incident" subtitle="creates an event in the alerts queue">
        <form method="POST" action="{{ route('school.safety.incident') }}" class="space-y-2 text-sm">
            @csrf
            <input name="title" placeholder="Incident title (e.g. Fire drill, Locked classroom)" required
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="description" placeholder="Description" rows="3"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <select name="severity"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="info">Info</option>
                <option value="warn">Warn</option>
                <option value="critical">Critical</option>
            </select>
            <button class="inline-flex items-center gap-1.5 bg-rose-600 text-white rounded-md px-3 py-1.5 font-medium hover:bg-rose-700 active:translate-y-[1px] transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                Log incident
            </button>
        </form>
    </x-card>

    <x-card title="Broadcast" subtitle="push to a channel · parents / staff / general">
        <form method="POST" action="{{ route('school.safety.broadcast') }}" class="space-y-2 text-sm">
            @csrf
            <select name="channel"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="safety">Safety</option>
                <option value="parents">Parents</option>
                <option value="staff">Staff</option>
                <option value="general">General</option>
            </select>
            <input name="title" placeholder="Headline" required
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <textarea name="body" placeholder="Body" rows="3"
                      class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
            <button class="inline-flex items-center gap-1.5 bg-amber-600 text-white rounded-md px-3 py-1.5 font-medium hover:bg-amber-700 active:translate-y-[1px] transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l18-9-9 18-2-7-7-2Z"/></svg>
                Broadcast
            </button>
        </form>
    </x-card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
    <x-card title="Recent incidents" subtitle="newest first">
        @if ($recentIncidents->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($recentIncidents as $e)
                    <li class="px-1 py-2 flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 {{ $sevDot[$e->severity] ?? 'bg-zinc-400' }}"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-zinc-900 truncate">{{ $e->title }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                                <span class="uppercase tracking-wide">{{ $e->severity }}</span>
                                <span class="text-zinc-300">·</span>
                                <span>{{ $e->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No incidents logged yet.</div>
        @endif
    </x-card>

    <x-card title="Recent broadcasts" subtitle="newest first">
        @if ($broadcasts->count() > 0)
            <ul class="divide-y divide-zinc-100 -mx-1">
                @foreach ($broadcasts as $b)
                    <li class="px-1 py-2 flex items-start gap-2.5">
                        <span class="mt-1.5 w-1.5 h-1.5 rounded-full shrink-0 bg-zinc-400"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-zinc-900 truncate">{{ $b->title }}</div>
                            <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums flex items-center gap-2">
                                <span class="uppercase tracking-wide">{{ $b->channel }}</span>
                                <span class="text-zinc-300">·</span>
                                <span>{{ $b->sent_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-xs text-zinc-500 py-2">No broadcasts sent yet.</div>
        @endif
    </x-card>
</div>

@endsection
