@extends('layouts.shell')
@section('title', 'Laporan disiplin')
@section('subtitle', 'discipline cases · investigation queue')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Management / Discipline</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 cases <span class="text-zinc-400">on file.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="New case" subtitle="lodge a discipline incident">
    <form method="POST" action="{{ route('ipg.laporan-masalah-disiplin.store') }}" class="space-y-2 text-sm">
        @csrf
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
            <input type="number" name="trainee_id" required placeholder="Trainee ID"
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            <select name="category" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="absenteeism">Absenteeism</option><option value="misconduct">Misconduct</option><option value="uniform">Uniform</option><option value="other">Other</option>
            </select>
            <select name="severity" class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
                <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option>
            </select>
            <input type="date" name="incident_date" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            <input name="location" placeholder="Location"
                   class="bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        </div>
        <textarea name="description" rows="3" placeholder="Description of the incident…"
                  class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-2 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"></textarea>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Submit case</button>
    </form>
</x-card>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No cases yet</div>
    <div class="text-xs text-zinc-500 mt-1">Cases queue here once the IPG discipline workflow is wired (Phase 4).</div>
</div>

@endsection
