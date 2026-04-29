@extends('layouts.shell')
@section('title', 'Logbook / reflection')
@section('subtitle', 'weekly trainee reflections · pensyarah review')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Practicum / Logbook</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $entries->count() }} {{ Str::plural('entry', $entries->count()) }}
            <span class="text-zinc-400">submitted.</span>
        </h1>
    </div>
</div>

@if ($entries->count() > 0)
    <div class="space-y-3">
        @foreach ($entries as $entry)
            @php $reviewed = $entry->reviewed_at !== null; @endphp
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card p-4">
                <div class="flex items-start justify-between gap-3 mb-2 flex-wrap">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-zinc-700 bg-zinc-50 border-zinc-200">
                            Week {{ $entry->week_number }}
                        </span>
                        <span class="text-sm font-medium text-zinc-900">{{ $entry->placement?->trainee?->name }}</span>
                        @if ($entry->submitted_at)
                            <span class="text-[10px] font-mono tabular-nums text-zinc-400">submitted {{ $entry->submitted_at->format('M j') }}</span>
                        @endif
                    </div>
                    @if ($reviewed)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-emerald-700 bg-emerald-50 border-emerald-200">
                            <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                            reviewed
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase tracking-wider rounded px-1.5 py-0.5 border text-amber-700 bg-amber-50 border-amber-200">
                            <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse"></span>
                            awaiting review
                        </span>
                    @endif
                </div>

                <div class="text-sm text-zinc-800 leading-relaxed">{{ $entry->reflection_text }}</div>

                @if ($entry->review_comment)
                    <div class="mt-3 pt-3 border-t border-zinc-100">
                        <div class="text-[10px] uppercase tracking-[0.14em] text-emerald-700 font-semibold mb-1">Pensyarah comment</div>
                        <div class="text-xs text-zinc-700 leading-relaxed">{{ $entry->review_comment }}</div>
                        @if ($entry->reviewer)
                            <div class="text-[10px] text-zinc-400 mt-1.5 font-mono tabular-nums">— {{ $entry->reviewer->name }} · {{ $entry->reviewed_at?->format('M j') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No logbook entries yet</div>
        <div class="text-xs text-zinc-500 mt-1">Trainees submit weekly reflections during their practicum window.</div>
    </div>
@endif

@endsection
