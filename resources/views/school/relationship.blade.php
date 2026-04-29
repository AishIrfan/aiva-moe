@extends('layouts.shell')
@section('title', 'Relationship mapping')
@section('subtitle', 'student-incident-camera connections')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Relationship graph</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            <span class="font-mono tabular-nums">{{ count($nodes) }}</span> nodes,
            <span class="font-mono tabular-nums text-zinc-400">{{ count($edges) }}</span> edges.
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3">
    <div class="rounded-xl border border-zinc-200 bg-white p-4">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Nodes</div>
        <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight text-zinc-900">{{ count($nodes) }}</div>
        <div class="text-[11px] text-zinc-500 mt-1">students · incidents · zones</div>
    </div>
    <div class="rounded-xl border border-zinc-200 bg-white p-4">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Edges</div>
        <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight text-zinc-900">{{ count($edges) }}</div>
        <div class="text-[11px] text-zinc-500 mt-1">attended · involved · sighted</div>
    </div>
    <div class="rounded-xl border border-zinc-200 bg-white p-4">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">Density</div>
        <div class="font-mono tabular-nums text-3xl font-semibold tracking-tight text-zinc-900">
            {{ count($nodes) > 0 ? round(count($edges) / max(1, count($nodes)), 2) : '0.00' }}
        </div>
        <div class="text-[11px] text-zinc-500 mt-1">edges per node</div>
    </div>
</div>

<x-card title="Graph data" subtitle="raw nodes and edges in JSON">
    <details class="group">
        <summary class="cursor-pointer text-xs text-emerald-700 hover:text-emerald-800 inline-flex items-center gap-1 font-medium">
            <svg class="w-3 h-3 group-open:rotate-90 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6 6 6-6 6"/></svg>
            Show JSON
        </summary>
        <pre class="mt-3 overflow-auto max-h-[60vh] bg-zinc-900 text-zinc-100 p-3 rounded-lg text-[11px] font-mono leading-relaxed">{{ json_encode(['nodes' => $nodes, 'edges' => $edges], JSON_PRETTY_PRINT) }}</pre>
    </details>
</x-card>

@endsection
