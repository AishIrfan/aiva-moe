@extends('layouts.shell')
@section('title', 'Chat')
@section('subtitle', 'pensyarah ↔ trainee threads · broadcasts')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Communication / Chat</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 threads <span class="text-zinc-400">open.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
    <div class="lg:col-span-4">
        <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
            <div class="px-3 py-2 border-b border-zinc-100 bg-zinc-50/60 text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500">Conversations</div>
            <div class="px-3 py-6 text-center text-xs text-zinc-500">No conversations yet.</div>
        </div>
    </div>
    <div class="lg:col-span-8">
        <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-zinc-50 border border-zinc-200 mb-3">
                <svg class="w-5 h-5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v11H8l-4 4V5Z"/></svg>
            </div>
            <div class="text-sm font-medium text-zinc-900">Pick a conversation</div>
            <div class="text-xs text-zinc-500 mt-1">Conversations land once the IPG messaging pipeline is wired (Phase 4).</div>
        </div>
    </div>
</div>

<x-card class="mt-3" title="Broadcast" subtitle="send a message to a trainee audience">
    <form method="POST" action="{{ route('ipg.chat.broadcast') }}" class="grid grid-cols-2 md:grid-cols-7 gap-2 text-sm">
        @csrf
        <select name="audience" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="all_trainees">All trainees</option>
            <option value="cohort">Cohort</option>
            <option value="program">Program</option>
        </select>
        <input name="title" required placeholder="Title"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="body" required placeholder="Body"
               class="md:col-span-3 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Send</button>
    </form>
</x-card>

@endsection
