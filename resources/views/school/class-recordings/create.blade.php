@extends('layouts.shell')
@section('title', 'Upload class recording')
@section('subtitle', 'manual upload — drop the video file and metadata')

@section('content')

<div class="mb-5">
    <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">
        <a href="{{ route('school.class-recordings.index') }}" class="hover:text-zinc-700">Class Recordings</a>
        <span class="text-zinc-300 mx-1">›</span>
        Upload
    </div>
    <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
        Upload <span class="text-zinc-400">a recording.</span>
    </h1>
</div>

@if ($errors->any())
    <div class="mb-4 px-3 py-2 rounded-md bg-rose-50 border border-rose-200 text-rose-800 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
@endif

<x-card title="Recording details" subtitle="all fields server-validated; file is MIME-sniffed not extension-trusted">
    <form method="POST" action="{{ route('school.class-recordings.store') }}" enctype="multipart/form-data" class="space-y-4 text-sm">
        @csrf

        <div>
            <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Video file</label>
            <input type="file" name="file" accept="{{ implode(',', $settings['class_recording_allowed_mime_types']) }}" required
                   class="block w-full text-xs text-zinc-700 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:text-white file:px-3 file:py-1.5 file:text-xs file:font-medium hover:file:bg-zinc-800"/>
            <div class="text-[11px] text-zinc-500 mt-1">
                Accepted: {{ implode(', ', $settings['class_recording_allowed_mime_types']) }} ·
                Max: {{ $settings['class_recording_max_file_size_mb'] }} MB
                @php $effectiveCap = min($settings['class_recording_max_file_size_mb'], (int) ini_get('upload_max_filesize')); @endphp
                @if ((int) ini_get('upload_max_filesize') < $settings['class_recording_max_file_size_mb'])
                    <span class="text-amber-700">(server PHP cap: {{ ini_get('upload_max_filesize') }} — chunked upload deferred to v2)</span>
                @endif
            </div>
            @if (! $settings['class_recording_audio_enabled'])
                <div class="mt-2 px-3 py-2 rounded-md bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    <strong>Audio is disabled for this school.</strong>
                    Please ensure your file does not contain an audio track. (v1 cannot reliably reject audio-bearing files at the server — content inspection is expensive — so this is a soft compliance warning.)
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Class</label>
                <select name="school_class_id" class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                    <option value="">— Not specified —</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}" @selected(old('school_class_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="255"
                       placeholder="e.g. Mathematics — Trigonometry"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
        </div>

        <div>
            <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Teacher</label>
            <select name="teacher_user_id" required class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                @php $defaultTeacherId = old('teacher_user_id', auth()->user()->role === \App\Models\User::ROLE_TEACHER ? auth()->id() : null); @endphp
                @foreach ($teachers as $t)
                    <option value="{{ $t->id }}" @selected($defaultTeacherId == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Started at</label>
                <input type="datetime-local" name="started_at" required
                       value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Ended at <span class="text-zinc-400 font-normal normal-case">(optional)</span></label>
                <input type="datetime-local" name="ended_at"
                       value="{{ old('ended_at') }}"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-4 py-2 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Upload</button>
            <a href="{{ route('school.class-recordings.index') }}" class="text-xs text-zinc-500 hover:text-zinc-900">Cancel</a>
        </div>
    </form>
</x-card>

@endsection
