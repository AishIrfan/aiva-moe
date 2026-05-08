@extends('layouts.shell')
@section('title', 'Recording detail')
@section('subtitle', 'playback · metadata · audit trail')

@section('content')

<div class="mb-5">
    <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">
        <a href="{{ route('school.class-recordings.index') }}" class="hover:text-zinc-700">Class Recordings</a>
        <span class="text-zinc-300 mx-1">›</span>
        {{ $recording->started_at?->format('Y-m-d H:i') ?? 'Detail' }}
    </div>
    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-zinc-900">
        {{ $recording->subject ?: 'Untitled recording' }}
        <span class="text-zinc-400 font-normal text-base ml-2">{{ $recording->schoolClass?->name }}</span>
    </h1>
</div>

@if (session('status'))
    <div class="mb-4 px-3 py-2 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">{{ session('status') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Player --}}
    <div class="lg:col-span-2">
        <x-card title="Playback" subtitle="auth-gated stream · HTTP range supported">
            @if ($recording->isPlayable())
                <video controls preload="metadata" class="w-full rounded-md bg-black aspect-video"
                       src="{{ route('school.class-recordings.stream', $recording) }}">
                    Your browser does not support the video tag.
                </video>
            @else
                <div class="aspect-video rounded-md bg-zinc-900 text-zinc-300 flex items-center justify-center text-sm">
                    @switch($recording->status)
                        @case('archived')   This recording was auto-deleted under retention policy. Metadata kept for audit. @break
                        @case('processing') Recording is processing — try again shortly. @break
                        @case('uploading')  Upload still in progress. @break
                        @case('failed')     Upload failed — file may be missing on storage. @break
                        @default            Recording is not currently playable (status: {{ $recording->status }}).
                    @endswitch
                </div>
            @endif
        </x-card>

        {{-- Action bar (gated by §7) --}}
        @if (in_array($tier, ['moe','school_admin'], true))
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($recording->isPlayable())
                    <a href="{{ route('school.class-recordings.download', $recording) }}"
                       class="inline-flex items-center gap-1.5 bg-white border border-zinc-200 hover:border-zinc-300 rounded-md px-3 py-1.5 text-xs font-medium text-zinc-900 transition">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14"/></svg>
                        Download
                    </a>
                @endif

                @if (! $recording->preserved)
                    <form method="POST" action="{{ route('school.class-recordings.preserve', $recording) }}" class="inline-flex"
                          onsubmit="this.querySelector('[name=reason]').value = prompt('Preservation reason (required — appears in audit log):') || ''; if (! this.querySelector('[name=reason]').value) return false;">
                        @csrf
                        <input type="hidden" name="reason"/>
                        <button class="inline-flex items-center gap-1.5 bg-amber-600 text-white hover:bg-amber-500 rounded-md px-3 py-1.5 text-xs font-medium transition">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2 3 7h7l-5.5 4 2 8L12 17l-6.5 4 2-8L2 9h7l3-7Z"/></svg>
                            Preserve
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('school.class-recordings.unpreserve', $recording) }}"
                          onsubmit="return confirm('Un-preserve? This recording will be subject to auto-delete again.');">
                        @csrf
                        <button class="inline-flex items-center gap-1.5 bg-white border border-amber-300 text-amber-800 hover:border-amber-400 rounded-md px-3 py-1.5 text-xs font-medium transition">
                            Un-preserve
                        </button>
                    </form>
                @endif

                @if (! $recording->trashed())
                    <form method="POST" action="{{ route('school.class-recordings.destroy', $recording) }}"
                          onsubmit="return confirm('Delete this recording permanently? File will be removed from storage. Metadata will be retained for audit. This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex items-center gap-1.5 bg-white border border-rose-200 text-rose-700 hover:border-rose-300 rounded-md px-3 py-1.5 text-xs font-medium transition">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        @elseif ($tier === 'teacher')
            <div class="mt-3 px-3 py-2 rounded-md bg-zinc-50 border border-zinc-200 text-zinc-600 text-xs">
                View-only access. Download / preserve / delete are restricted to school admins.
            </div>
        @endif
    </div>

    {{-- Metadata + Audit panels --}}
    <div class="space-y-4">
        <x-card title="Metadata">
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Class</dt>
                    <dd class="text-zinc-900 text-right">{{ $recording->schoolClass?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Teacher</dt>
                    <dd class="text-zinc-900 text-right">{{ $recording->teacher?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Subject</dt>
                    <dd class="text-zinc-900 text-right">{{ $recording->subject ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Started</dt>
                    <dd class="text-zinc-900 text-right font-mono text-xs">{{ $recording->started_at?->format('Y-m-d H:i:s') }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Ended</dt>
                    <dd class="text-zinc-900 text-right font-mono text-xs">{{ $recording->ended_at?->format('Y-m-d H:i:s') ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Duration</dt>
                    <dd class="text-zinc-900 text-right font-mono">{{ $recording->duration_seconds ? gmdate('H:i:s', $recording->duration_seconds) : '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Status</dt>
                    <dd class="text-zinc-900 text-right text-xs uppercase tracking-wide">{{ str_replace('_',' ', $recording->status) }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Source</dt>
                    <dd class="text-zinc-900 text-right text-xs">{{ str_replace('_',' ', $recording->created_via) }}</dd>
                </div>
                @if ($recording->file_size_bytes)
                    <div class="flex justify-between gap-2">
                        <dt class="text-zinc-500">Size</dt>
                        <dd class="text-zinc-900 text-right font-mono text-xs">{{ number_format($recording->file_size_bytes / 1048576, 1) }} MB</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-2">
                    <dt class="text-zinc-500">Retention</dt>
                    <dd class="text-zinc-900 text-right text-xs">
                        @if ($recording->preserved)
                            <span class="text-amber-700 font-medium">PRESERVED — exempt</span>
                        @else
                            {{ $recording->retention_expires_at?->format('Y-m-d') ?? '—' }}
                        @endif
                    </dd>
                </div>
                @if ($recording->preserved)
                    <div class="pt-2 mt-2 border-t border-zinc-100">
                        <div class="text-zinc-500 text-xs uppercase tracking-wide mb-1">Preservation</div>
                        <div class="text-zinc-700 text-xs">By {{ $recording->preservedBy?->name ?? '—' }} on {{ $recording->preserved_at?->format('Y-m-d') }}</div>
                        @if ($recording->preserved_reason)
                            <div class="mt-1 text-zinc-600 text-xs italic">"{{ $recording->preserved_reason }}"</div>
                        @endif
                    </div>
                @endif
            </dl>
        </x-card>

        <x-card title="Audit trail" subtitle="who has accessed this recording">
            @php $logs = $recording->auditLogs()->with('user:id,name')->orderByDesc('created_at')->limit(50)->get(); @endphp
            @if ($logs->isEmpty())
                <div class="text-zinc-500 text-xs">No audit entries yet.</div>
            @else
                <ul class="text-xs space-y-1.5 font-mono">
                    @foreach ($logs as $l)
                        <li class="flex justify-between gap-2">
                            <span class="text-zinc-700">{{ str_replace('class_recording.','', $l->action) }} <span class="text-zinc-400">·</span> {{ $l->user?->name ?? 'system' }}</span>
                            <span class="text-zinc-400 tabular-nums">{{ $l->created_at?->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-card>
    </div>
</div>

@endsection
