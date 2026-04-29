@extends('layouts.shell')
@section('title', 'Enrollment')
@section('subtitle', 'semester intake · cohort assignment')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Academics / Enrollment</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Onboard <span class="text-zinc-400">an intake into a cohort.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="Bulk intake" subtitle="onboard new trainees into a cohort for the current semester">
    <form method="POST" action="{{ route('ipg.enrollment.assign') }}" class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
        @csrf
        <select name="cohort_id" required class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Cohort…</option>
            @foreach ($cohorts as $c)
                <option value="{{ $c->id }}">{{ $c->display_name }}</option>
            @endforeach
        </select>
        <select name="semester_id" required class="bg-zinc-50 border border-zinc-200 rounded-md px-2 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300">
            <option value="">Semester…</option>
            @foreach ($semesters as $s)
                <option value="{{ $s->id }}" @selected($s->is_current)>{{ $s->code }} ({{ $s->name }})</option>
            @endforeach
        </select>
        <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Open intake</button>
    </form>
</x-card>

<x-card title="Recently enrolled" subtitle="trainees added in the current semester">
    <div class="text-xs text-zinc-500 py-6 text-center">
        Live feed comes online once the enrollment workflow is wired (Phase 4).
        Use the <a href="{{ route('ipg.trainees') }}" class="text-emerald-700 hover:text-emerald-800 font-medium">Trainees roster</a> to see the existing seed.
    </div>
</x-card>

@endsection
