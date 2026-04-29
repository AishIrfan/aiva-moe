@extends('layouts.shell')
@section('title', 'Face enrollment')
@section('subtitle', 'register student faces with SenseStudio')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Biometrics</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Enroll a face,
            <span class="text-zinc-400">link to a student.</span>
        </h1>
    </div>
</div>

{{-- Connection status banner --}}
<div class="mb-3 rounded-xl border {{ $connected ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} p-3 flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
        <span class="relative flex h-2 w-2">
            @if ($connected)
                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
            @else
                <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
            @endif
        </span>
        <div>
            <div class="text-xs font-medium {{ $connected ? 'text-emerald-800' : 'text-rose-800' }}">
                SenseStudio · {{ $connected ? 'Connected' : 'Not connected' }}
            </div>
            <div class="text-[11px] {{ $connected ? 'text-emerald-700/70' : 'text-rose-700/70' }} mt-0.5">
                {{ $connected ? 'Face library is reachable. New enrollments push instantly.' : 'Configure the SenseStudio endpoint in school settings.' }}
            </div>
        </div>
    </div>
    <a href="{{ route('school.settings') }}"
       class="text-xs font-medium text-zinc-700 hover:text-zinc-900 bg-white border border-zinc-200 hover:border-zinc-300 rounded-md px-2.5 py-1 transition">Configure</a>
</div>

{{-- Enrollment form --}}
<x-card title="Enroll student face" subtitle="upload a clear, front-facing photo">
    <form method="POST" action="{{ route('school.face-enrollment.person') }}" enctype="multipart/form-data" class="space-y-3">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Student</label>
                <select name="student_id" required
                        class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:bg-white focus:border-zinc-300">
                    @foreach ($students as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} · {{ $s->student_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Library ID</label>
                <input name="library_id" required placeholder="lib-abc123"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 text-sm font-mono focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
        </div>

        <div>
            <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Face image</label>
            <div class="flex items-center gap-3 p-3 rounded-lg border border-dashed border-zinc-300 bg-zinc-50/60">
                <svg class="w-7 h-7 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="0.5" fill="currentColor"/><circle cx="15" cy="10" r="0.5" fill="currentColor"/>
                </svg>
                <input type="file" name="image" accept="image/*" required
                       class="text-sm text-zinc-700 file:mr-3 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:bg-zinc-900 file:text-white file:text-xs file:font-medium hover:file:bg-zinc-800 file:cursor-pointer file:transition"/>
            </div>
            <div class="text-[11px] text-zinc-500 mt-1.5">JPG or PNG, ideally 800×800 or larger, front-facing, well-lit.</div>
        </div>

        <button class="inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-md px-4 py-2 text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
            Enroll
        </button>
    </form>

    <div class="mt-5 pt-5 border-t border-zinc-100">
        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-2">Roster</div>
        {{ $students->links() }}
    </div>
</x-card>

@endsection
