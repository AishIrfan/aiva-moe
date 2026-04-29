@extends('layouts.shell')
@section('title', 'Grades & classes')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Academics</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ count($grades) }} grades,
            <span class="text-zinc-400">{{ collect($grades)->sum(fn($g) => $g->classes->count()) }} classes.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-3">
    <x-card title="Add grade" subtitle="e.g. Form 1, Standard 4">
        <form method="POST" action="{{ route('school.grades.store') }}" class="space-y-2 text-sm">
            @csrf
            <input name="name" placeholder="Name" required
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
            <input name="level" type="number" min="0" placeholder="Level (0-12)" required
                   class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
            <button class="w-full inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Add grade</button>
        </form>
    </x-card>

    <x-card class="lg:col-span-2" title="Add class" subtitle="register a section under an existing grade">
        <form method="POST" action="{{ route('school.classes.store') }}" class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
            @csrf
            <select name="grade_id" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                @foreach ($grades as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
            </select>
            <input name="name" placeholder="Class (e.g. 1A)" required
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
            <input name="capacity" type="number" value="40"
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:bg-white focus:border-zinc-300 focus:outline-none transition"/>
            <select name="homeroom_teacher_id" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="">Homeroom teacher…</option>
                @foreach ($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
            </select>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Add</button>
        </form>
    </x-card>
</div>

@if (count($grades) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($grades as $g)
            <div class="bg-white border border-zinc-200 rounded-xl shadow-card overflow-hidden">
                <div class="px-4 py-3 border-b border-zinc-100 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400">Level {{ $g->level }}</div>
                        <div class="text-sm font-semibold text-zinc-900">{{ $g->name }}</div>
                    </div>
                    <span class="font-mono tabular-nums text-xs text-zinc-500">{{ $g->classes->count() }} classes</span>
                </div>
                <ul class="divide-y divide-zinc-50 text-sm">
                    @forelse ($g->classes as $c)
                        <li class="px-4 py-2 flex items-center justify-between">
                            <span class="font-medium text-zinc-900">{{ $c->name }}</span>
                            <span class="text-[11px] text-zinc-500 font-mono tabular-nums">cap {{ $c->capacity }} · #{{ $c->id }}</span>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-xs text-zinc-500">No classes yet under this grade.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
        <div class="text-sm font-medium text-zinc-900">No grades defined yet</div>
        <div class="text-xs text-zinc-500 mt-1">Use "Add grade" above to start.</div>
    </div>
@endif

@endsection
