@extends('layouts.shell')
@section('title', 'IPG · Overview')
@section('subtitle', 'Institut Pendidikan Guru — operations console')

@php
    use App\Models\Campus;
    use App\Models\Cohort;
    use App\Models\Pensyarah;
    use App\Models\Trainee;
    use App\Models\Semester;

    $campusId = session('campus_id');
    $campus   = $campusId ? Campus::find($campusId) : null;

    // KPIs only meaningful when a campus is selected. BPG-without-campus shows
    // a different state (pick a campus first).
    $traineeCount   = $campus ? Trainee::where('campus_id', $campus->id)->count() : 0;
    $cohortCount    = $campus ? Cohort::where('campus_id', $campus->id)->count() : 0;
    $pensyarahCount = $campus ? Pensyarah::where('campus_id', $campus->id)->count() : 0;
    $coordinator    = $campus ? Pensyarah::where('campus_id', $campus->id)->where('is_practicum_coordinator', true)->first() : null;
    $semester       = $campus?->currentSemester();
@endphp

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-6">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">
            @if ($campus)
                {{ $campus->name }} / Overview
            @else
                Institut Pendidikan Guru / Overview
            @endif
        </div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            @if ($campus)
                {{ $traineeCount }} trainees, <span class="text-zinc-400 cursor-blink">{{ $cohortCount }} {{ Str::plural('cohort', $cohortCount) }}.</span>
            @else
                Pick a <span class="text-zinc-400 cursor-blink">campus.</span>
            @endif
        </h1>
    </div>
    <div class="flex items-center gap-2 text-xs text-zinc-500">
        <span class="inline-flex items-center gap-1.5 font-mono tabular-nums">
            <span class="relative flex h-1.5 w-1.5">
                <span class="absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75 animate-ping"></span>
                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-amber-500"></span>
            </span>
            scaffold mode
        </span>
        <span class="text-zinc-300">·</span>
        <span class="font-mono tabular-nums">{{ now()->format('H:i') }} MYT</span>
    </div>
</div>

{{-- Pick-a-campus prompt for BPG users without a campus context --}}
@if (! $campus)
    <a href="{{ route('bpg.campuses') }}"
       class="block mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 hover:border-amber-300 transition">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
            <div class="text-xs text-amber-900">
                <div class="font-medium">No campus selected</div>
                <div class="text-amber-800/80 mt-0.5">Pick an IPG campus from the BPG → Campuses directory to unlock the rest of the IPG console.</div>
            </div>
        </div>
    </a>
@endif

{{-- Scaffold notice banner --}}
<div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/>
    </svg>
    <div class="text-xs text-amber-900">
        <div class="font-medium">IPG module is currently a scaffold</div>
        <div class="text-amber-800/80 mt-0.5">
            Foundations are wired (Campus · Semester · Cohort · Trainee · Pensyarah models seeded). Each page in the sidebar is reachable; module logic gets promoted from placeholder to live in the upcoming phases (Practicum, Transcripts, Hostel etc.).
        </div>
    </div>
</div>

{{-- KPI bento --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 stagger-in">
    <x-card class="hover-lift">
        <x-slot:eyebrow>Trainees enrolled</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight {{ $campus ? 'text-zinc-900' : 'text-zinc-300' }}">{{ $campus ? number_format($traineeCount) : '—' }}</span>
            <span class="text-xs text-zinc-500">{{ $campus ? 'active' : 'pick campus' }}</span>
        </div>
        @if ($campus)
            <a href="{{ route('ipg.trainees') }}" class="inline-flex items-center gap-1 text-[11px] mt-4 text-emerald-700 hover:text-emerald-800 font-medium">
                Open roster
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        @else
            <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">awaiting campus context</div>
        @endif
    </x-card>

    <x-card class="hover-lift">
        <x-slot:eyebrow>Cohorts (PISMP)</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight {{ $campus ? 'text-zinc-900' : 'text-zinc-300' }}">{{ $campus ? $cohortCount : '—' }}</span>
            <span class="text-xs text-zinc-500">major × intake</span>
        </div>
        @if ($campus)
            <a href="{{ route('ipg.grades-cohorts') }}" class="inline-flex items-center gap-1 text-[11px] mt-4 text-emerald-700 hover:text-emerald-800 font-medium">
                Open cohorts
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        @else
            <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">—</div>
        @endif
    </x-card>

    <x-card class="hover-lift">
        <x-slot:eyebrow>Pensyarah</x-slot:eyebrow>
        <div class="flex items-baseline gap-2">
            <span class="font-mono tabular-nums text-4xl font-semibold tracking-tight {{ $campus ? 'text-zinc-900' : 'text-zinc-300' }}">{{ $campus ? $pensyarahCount : '—' }}</span>
            <span class="text-xs text-zinc-500">on faculty</span>
        </div>
        @if ($coordinator)
            <div class="text-[11px] text-zinc-500 mt-4 truncate">
                <span class="text-[10px] uppercase tracking-wider text-zinc-400 font-semibold">Penyelaras Praktikum</span>
                <div class="font-medium text-zinc-900 mt-0.5">{{ $coordinator->name }}</div>
            </div>
        @else
            <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">{{ $campus ? 'no coordinator assigned' : '—' }}</div>
        @endif
    </x-card>

    <x-card class="hover-lift">
        <x-slot:eyebrow>Current semester</x-slot:eyebrow>
        @if ($semester)
            <div class="font-mono tabular-nums text-2xl font-semibold tracking-tight text-zinc-900">{{ $semester->code }}</div>
            <div class="text-[11px] text-zinc-500 mt-1 truncate">{{ $semester->name }}</div>
            <div class="text-[11px] text-zinc-400 mt-3 font-mono tabular-nums">
                {{ $semester->start_date->format('M j') }} → {{ $semester->end_date->format('M j, Y') }}
            </div>
        @else
            <div class="font-mono tabular-nums text-4xl font-semibold tracking-tight text-zinc-300">—</div>
            <div class="text-[11px] text-zinc-400 mt-4 font-mono tabular-nums">{{ $campus ? 'no current semester' : '—' }}</div>
        @endif
    </x-card>
</div>

{{-- Module map + wiring checklist --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mt-3">

    <x-card class="lg:col-span-7" title="Module map" subtitle="every IPG page is reachable from the sidebar">
        @php
            $modules = [
                'Campus System' => [['Academic Calendar', 'ipg.academic-calendar']],
                'Academics'     => [['Grades & Cohorts', 'ipg.grades-cohorts'], ['Enrollment', 'ipg.enrollment'], ['Timetables', 'ipg.timetables'], ['Transcripts', 'ipg.transcripts'], ['Co-curriculum', 'ipg.cocurriculum'], ['Research', 'ipg.research']],
                'Practicum'     => [['Placements', 'ipg.practicum.placements'], ['Supervisors', 'ipg.practicum.supervisors'], ['Observations', 'ipg.practicum.observations'], ['Evaluations', 'ipg.practicum.evaluations'], ['Logbook', 'ipg.practicum.logbook'], ['School Coordination', 'ipg.practicum.coordination']],
                'Trainees'      => [['Trainees roster', 'ipg.trainees'], ['Trainee 360', 'ipg.trainee-360'], ['Leaves', 'ipg.leaves'], ['Assistance', 'ipg.assistance'], ['Hostel', 'ipg.hostel']],
                'Communication' => [['Chat', 'ipg.chat'], ['Documents', 'ipg.documents']],
                'Management'    => [['Events', 'ipg.events-management'], ['Surat Cuti / MC', 'ipg.surat-cuti-mc'], ['Laporan Disiplin', 'ipg.laporan-masalah-disiplin'], ['Attendance', 'ipg.attendance'], ['Reports', 'ipg.reports'], ['Settings', 'ipg.settings']],
            ];
        @endphp

        <div class="-mx-1 divide-y divide-zinc-100">
            @foreach ($modules as $groupName => $items)
                <div class="px-1 py-2.5">
                    <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-1.5">{{ $groupName }}</div>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($items as $item)
                            <a href="{{ route($item[1]) }}"
                               class="inline-flex items-center gap-1 text-xs text-zinc-700 hover:text-emerald-700 bg-zinc-50 hover:bg-emerald-50 border border-zinc-200 hover:border-emerald-200 rounded-md px-2.5 py-1 transition">
                                {{ $item[0] }}
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card class="lg:col-span-5" title="Wiring checklist" subtitle="path from scaffold to live">
        <ul class="space-y-2.5 mt-1 text-sm">
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <div>
                    <div class="text-zinc-900 font-medium">Routes &amp; nav · §7 target</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">All paths resolve, sidebar matches the checklist.</div>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <div>
                    <div class="text-zinc-900 font-medium">Foundation data model</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Campus · Semester · Program · Cohort · Trainee · Pensyarah seeded.</div>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                <div>
                    <div class="text-zinc-900 font-medium">Promote KEEP modules</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Adapt school views to trainees/cohorts/semesters terminology.</div>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-zinc-300 shrink-0"></span>
                <div>
                    <div class="text-zinc-900 font-medium">Build NEW modules</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Transcripts · Co-curriculum · Research · Hostel · Practicum × 6.</div>
                </div>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-zinc-300 shrink-0"></span>
                <div>
                    <div class="text-zinc-900 font-medium">Cross-mode placement projection</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Trainee tag + placement letter on host school during practicum window.</div>
                </div>
            </li>
        </ul>
    </x-card>
</div>

@endsection
