@extends('layouts.shell')
@section('title', 'Class Recordings')
@section('subtitle', 'classroom video recordings · safety & evidentiary')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">Safety / Class Recordings</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $recordings->total() }}
            <span class="text-zinc-400">recording{{ $recordings->total() === 1 ? '' : 's' }}{{ $tier === 'teacher' ? ' (yours)' : ' on file' }}.</span>
        </h1>
    </div>
    @if (in_array($tier, ['moe','school_admin','teacher'], true))
        <a href="{{ route('school.class-recordings.create') }}"
           class="inline-flex items-center gap-2 bg-zinc-900 text-white rounded-md px-3 py-2 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Upload recording
        </a>
    @endif
</div>

@if (session('status'))
    <div class="mb-4 px-3 py-2 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">{{ session('status') }}</div>
@endif

{{-- Filters --}}
<form method="GET" class="grid grid-cols-2 md:grid-cols-6 gap-2 text-sm mb-4 p-3 rounded-lg border border-zinc-200 bg-zinc-50/60">
    <select name="class" class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs">
        <option value="">All classes</option>
        @foreach ($classes as $c)
            <option value="{{ $c->id }}" @selected(request('class') == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    @if ($tier !== 'teacher')
        <select name="teacher" class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs">
            <option value="">All teachers</option>
            @foreach ($teachers as $t)
                <option value="{{ $t->id }}" @selected(request('teacher') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
    @endif
    <select name="status" class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs">
        <option value="">All statuses</option>
        @foreach (\App\Models\ClassRecording::STATUSES as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="preserved" class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs">
        <option value="">Any preservation</option>
        <option value="yes" @selected(request('preserved') === 'yes')>Preserved only</option>
        <option value="no"  @selected(request('preserved') === 'no')>Not preserved</option>
    </select>
    <input type="date" name="from" value="{{ request('from') }}"
           class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs"/>
    <input type="date" name="to" value="{{ request('to') }}"
           class="bg-white border border-zinc-200 rounded-md px-2 py-1.5 text-xs"/>
    <button type="submit" class="md:col-span-1 col-span-2 inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 text-xs font-medium hover:bg-zinc-800">Filter</button>
</form>

<div class="rounded-lg border border-zinc-200 bg-white overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-zinc-50 border-b border-zinc-200">
            <tr class="text-[10px] uppercase tracking-[0.14em] text-zinc-500 font-semibold">
                <th class="text-left px-3 py-2">When</th>
                <th class="text-left px-3 py-2">Class</th>
                <th class="text-left px-3 py-2">Teacher</th>
                <th class="text-left px-3 py-2">Subject</th>
                <th class="text-right px-3 py-2 font-mono">Duration</th>
                <th class="text-left px-3 py-2">Status</th>
                <th class="text-left px-3 py-2">Retention</th>
                <th class="text-right px-3 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            @forelse ($recordings as $r)
                <tr class="hover:bg-zinc-50/60">
                    <td class="px-3 py-2 font-mono text-xs tabular-nums whitespace-nowrap">
                        {{ $r->started_at?->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-3 py-2 text-zinc-700">{{ $r->schoolClass?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-zinc-700">{{ $r->teacher?->name ?? '—' }}</td>
                    <td class="px-3 py-2 text-zinc-500">{{ $r->subject ?? '—' }}</td>
                    <td class="px-3 py-2 text-right font-mono tabular-nums text-zinc-600">
                        {{ $r->duration_seconds ? gmdate('H:i:s', $r->duration_seconds) : '—' }}
                    </td>
                    <td class="px-3 py-2">
                        @php
                            $tone = match ($r->status) {
                                'ready'      => 'bg-emerald-100 text-emerald-800',
                                'archived'   => 'bg-zinc-200 text-zinc-700',
                                'failed'     => 'bg-rose-100 text-rose-800',
                                'processing' => 'bg-amber-100 text-amber-800',
                                'uploading', 'recording' => 'bg-sky-100 text-sky-800',
                                default      => 'bg-zinc-100 text-zinc-700',
                            };
                        @endphp
                        <span class="inline-flex items-center text-[10px] uppercase tracking-wide font-semibold px-1.5 py-0.5 rounded {{ $tone }}">
                            {{ str_replace('_',' ', $r->status) }}
                        </span>
                        @if ($r->preserved)
                            <span class="inline-flex items-center ml-1 text-[10px] uppercase tracking-wide font-semibold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">preserved</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-zinc-500 text-xs">
                        @if ($r->preserved)
                            <span class="text-amber-700">exempt</span>
                        @elseif ($r->retention_expires_at)
                            {{ $r->retention_expires_at->format('Y-m-d') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('school.class-recordings.show', $r) }}"
                           class="text-xs font-medium text-zinc-900 hover:underline">Open →</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-3 py-8 text-center text-zinc-500 text-sm">
                        No recordings yet.
                        @if (in_array($tier, ['moe','school_admin','teacher'], true))
                            <a href="{{ route('school.class-recordings.create') }}" class="text-zinc-900 hover:underline font-medium">Upload one →</a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $recordings->links() }}
</div>

@endsection
