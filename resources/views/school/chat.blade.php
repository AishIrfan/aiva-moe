@extends('layouts.shell')
@section('title', 'Chat')
@section('subtitle', 'parent-school conversations · broadcasts')

@php
    $statusPill = [
        'open'    => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'pending' => 'text-amber-700 bg-amber-50 border-amber-200',
        'closed'  => 'text-zinc-700 bg-zinc-50 border-zinc-200',
    ];
@endphp

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Communication</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $conversations->total() }} threads
            <span class="text-zinc-400">with parents.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

    {{-- Conversation list --}}
    <div class="lg:col-span-4">
        <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-3 py-2 border-b border-zinc-100 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">Conversations</div>
            <ul class="divide-y divide-zinc-100 max-h-[60vh] overflow-y-auto">
                @forelse ($conversations as $c)
                    @php
                        $isActive = optional($active)->id === $c->id;
                        $title = $c->subject ?? ($c->student?->name ?? 'Conversation #' . $c->id);
                    @endphp
                    <li>
                        <a href="{{ url()->current() }}?conv={{ $c->id }}"
                           class="block px-3 py-2.5 transition {{ $isActive ? 'bg-emerald-50/60 nav-pill-active' : 'hover:bg-zinc-50' }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-sm font-medium {{ $isActive ? 'text-emerald-800' : 'text-zinc-900' }} truncate">{{ $title }}</div>
                                <span class="font-mono tabular-nums text-[10px] text-zinc-400 shrink-0">{{ $c->messages_count }}</span>
                            </div>
                            <div class="mt-1">
                                <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$c->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                                    {{ $c->status }}
                                </span>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-3 py-6 text-center text-xs text-zinc-500">No conversations.</li>
                @endforelse
            </ul>
            <div class="px-3 py-2 border-t border-zinc-100">{{ $conversations->links() }}</div>
        </div>
    </div>

    {{-- Active conversation --}}
    <div class="lg:col-span-8">
        @if ($active)
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden flex flex-col" style="height: min(70vh, 720px)">
                <div class="px-4 py-3 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400">Thread</div>
                        <div class="text-sm font-semibold text-zinc-900">{{ $active->subject ?? 'Conversation #' . $active->id }}</div>
                    </div>
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border {{ $statusPill[$active->status] ?? 'text-zinc-700 bg-zinc-50 border-zinc-200' }}">
                        {{ $active->status }}
                    </span>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 bg-zinc-50/40">
                    @foreach ($messages as $m)
                        @php $isStaff = in_array($m->sender_role, ['teacher','admin','school']); @endphp
                        <div class="flex {{ $isStaff ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%]">
                                <div class="text-[10px] uppercase tracking-wider font-semibold mb-1 {{ $isStaff ? 'text-emerald-700 text-right' : 'text-zinc-500' }}">
                                    {{ $m->sender_role }}
                                    @if ($m->flagged)
                                        <span class="ml-1 inline-flex items-center gap-1 text-rose-700">
                                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/></svg>
                                            flagged
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm rounded-2xl px-3 py-2 border {{ $isStaff ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white text-zinc-800 border-zinc-200' }}">
                                    {{ $m->body }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('school.chat.message', $active) }}" class="flex items-center gap-2 p-3 border-t border-zinc-100">
                    @csrf
                    <input name="body" required placeholder="Type a reply…"
                           class="flex-1 bg-zinc-50 border border-zinc-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
                    <button class="inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-lg px-4 py-1.5 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
                        Send
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4 20-7Z"/></svg>
                    </button>
                </form>
            </div>
        @else
            <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
                    <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v11H8l-4 4V5Z"/></svg>
                </div>
                <div class="text-sm font-medium text-zinc-900">Pick a conversation</div>
                <div class="text-xs text-zinc-500 mt-1">Tap a thread on the left to read or reply.</div>
            </div>
        @endif
    </div>
</div>

{{-- Broadcast --}}
<x-card class="mt-3" title="Broadcast" subtitle="send a message to a parent audience">
    <form method="POST" action="{{ route('school.chat.broadcast') }}" class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">
        @csrf
        <select name="audience" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="all_parents">All parents</option>
            <option value="class">Class</option>
            <option value="grade">Grade</option>
            <option value="custom">Custom</option>
        </select>
        <input name="title" required placeholder="Title"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="body" required placeholder="Body"
               class="md:col-span-3 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Send</button>
    </form>

    @if (count($broadcasts) > 0)
        <ul class="mt-4 -mx-1 divide-y divide-zinc-100">
            @foreach ($broadcasts as $b)
                <li class="px-1 py-2 flex items-center gap-3">
                    <span class="text-sm font-medium text-zinc-900 truncate flex-1">{{ $b->title }}</span>
                    <span class="text-[11px] text-zinc-500 font-mono tabular-nums">
                        {{ $b->audience }}
                        @if ($b->sent_at)
                            <span class="text-zinc-300">·</span>
                            {{ $b->sent_at?->diffForHumans() }}
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</x-card>

@endsection
