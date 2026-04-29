@extends('layouts.shell')
@section('title', 'Documents')
@section('subtitle', 'circulars, forms, policies')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Documents</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $documents->total() }} on file
            <span class="text-zinc-400">for distribution.</span>
        </h1>
    </div>
</div>

{{-- IPG cross-mode projection: incoming placement letters (§6.1) --}}
@if (! empty($placementLetters) && $placementLetters->count() > 0)
    <div class="mb-3 bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-zinc-100 bg-emerald-50/40">
            <svg class="w-4 h-4 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            <div class="text-[10px] uppercase tracking-[0.18em] font-semibold text-emerald-700">IPG placement letters · incoming</div>
            <span class="text-[10px] text-zinc-400 font-mono tabular-nums ml-auto">{{ $placementLetters->count() }} on file</span>
        </div>
        <div class="divide-y divide-zinc-100">
            @foreach ($placementLetters as $letter)
                @php $acknowledged = $letter->acknowledged_at !== null; @endphp
                <div class="px-4 py-3 flex items-start gap-3">
                    <span class="w-9 h-9 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 ring-1 ring-emerald-200">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-zinc-900">{{ str_replace('_', ' ', $letter->kind) }}</span>
                            <span class="text-[10px] font-mono tabular-nums text-zinc-500">re: {{ $letter->placement?->trainee?->name }}</span>
                        </div>
                        <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums">
                            from {{ $letter->placement?->trainee?->campus?->name }} ·
                            {{ $letter->sent_at ? 'sent ' . $letter->sent_at->format('M j, Y') : 'unsent' }}
                        </div>
                    </div>
                    @if ($acknowledged)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-emerald-700 bg-emerald-50 border-emerald-200 shrink-0">acknowledged</span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-amber-700 bg-amber-50 border-amber-200 shrink-0">
                            <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>pending
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

<x-card class="mb-3" title="Upload document" subtitle="circulars, permissions slips, policies">
    <form method="POST" action="{{ route('school.documents.store') }}" enctype="multipart/form-data"
          class="grid grid-cols-2 md:grid-cols-12 gap-2 text-sm items-center">
        @csrf
        <input name="title" required placeholder="Title"
               class="md:col-span-3 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="category" required value="general"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input type="file" name="file"
               class="md:col-span-4 text-xs text-zinc-700 file:mr-3 file:px-3 file:py-1 file:rounded-md file:border-0 file:bg-zinc-900 file:text-white file:text-xs file:font-medium hover:file:bg-zinc-800 file:cursor-pointer"/>
        <label class="md:col-span-2 flex items-center gap-1.5 text-xs">
            <input type="checkbox" name="requires_ack" value="1" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
            Requires ack
        </label>
        <button class="md:col-span-1 inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Upload</button>
    </form>
</x-card>

@if ($documents->count() > 0)
    <div class="bg-white border border-zinc-200 rounded-xl shadow-card divide-y divide-zinc-100 overflow-hidden">
        @foreach ($documents as $d)
            <div class="px-4 py-3 flex items-center gap-3">
                <span class="w-9 h-9 rounded-md bg-zinc-100 text-zinc-500 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6M8 13h8M8 17h6"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium text-zinc-900 truncate">{{ $d->title }}</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5 font-mono tabular-nums uppercase tracking-wide">{{ $d->category }}</div>
                </div>
                <div class="flex items-center gap-2 shrink-0 text-[11px] font-mono tabular-nums">
                    <div class="text-right">
                        <div class="text-[9px] uppercase tracking-wider text-zinc-400">Links</div>
                        <div class="text-zinc-700">{{ $d->links->count() }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] uppercase tracking-wider text-zinc-400">Acks</div>
                        <div class="text-zinc-700">{{ $d->acknowledgments->count() }}</div>
                    </div>
                </div>
                @if ($d->requires_ack)
                    <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-amber-700 bg-amber-50 border-amber-200 shrink-0">
                        ack required
                    </span>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-5">{{ $documents->links() }}</div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No documents uploaded yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use the form above to upload your first.</div>
    </div>
@endif

@endsection
