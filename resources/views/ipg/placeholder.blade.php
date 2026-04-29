@extends('layouts.shell')
@section('title', 'IPG · ' . $title)
@section('subtitle', $section)

@section('content')

{{-- Heading --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / {{ $section }}</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            {{ $title }}
            <span class="text-zinc-400 cursor-blink">scaffold.</span>
        </h1>
    </div>
    <a href="{{ route('ipg.overview') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-zinc-200 bg-white text-zinc-700 text-sm hover:border-zinc-300 transition">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        IPG overview
    </a>
</div>

{{-- Scaffold banner --}}
<div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3 flex items-start gap-3">
    <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/>
    </svg>
    <div class="text-xs text-amber-900">
        <div class="font-medium">Scaffold preview</div>
        <div class="text-amber-800/80 mt-0.5">{{ $blurb }}</div>
    </div>
</div>

{{-- Placeholder body — designed to feel like the eventual page without rendering empty data tables --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

    <x-card class="lg:col-span-8" title="What this page will show" subtitle="planned surface area">
        <ul class="space-y-2.5 mt-1 text-sm">
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="text-zinc-700">Mirrors the <span class="font-mono text-[12px] text-zinc-900 bg-zinc-100 rounded px-1">school.{{ $pageSlug }}</span> surface — same data shapes, same actions, scoped to IPG.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="text-zinc-700">Routes are live and addressable at <span class="font-mono text-[12px] text-zinc-900 bg-zinc-100 rounded px-1">/ipg/{{ $pageSlug }}</span> — both GET and action POSTs resolve.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                <span class="text-zinc-700">Action endpoints currently route to <span class="font-mono text-[12px] text-zinc-900 bg-zinc-100 rounded px-1">IPGController@stub</span> — they redirect back with a flash message until wired.</span>
            </li>
            <li class="flex items-start gap-2.5">
                <span class="mt-1.5 w-1.5 h-1.5 rounded-full bg-zinc-300 shrink-0"></span>
                <span class="text-zinc-700">Promote this page by replacing the <span class="font-mono text-[12px] text-zinc-900 bg-zinc-100 rounded px-1">{{ str_replace('-', '', \Illuminate\Support\Str::camel($pageSlug)) }}()</span> handler in <span class="font-mono text-[12px] text-zinc-900 bg-zinc-100 rounded px-1">App\Http\Controllers\IPG\IPGController</span>.</span>
            </li>
        </ul>
    </x-card>

    <x-card class="lg:col-span-4" title="Reference" subtitle="route + view paths">
        @php
            // Map IPG slug → school slug where a wired equivalent exists.
            // New IPG-only modules (Transcripts, Practicum, Hostel etc.) have no
            // School counterpart, so we just don't render the reference link.
            $ipgToSchool = [
                'enrollment'                 => 'enrollment',
                'timetables'                 => 'timetables',
                'leaves'                     => 'leaves',
                'assistance'                 => 'assistance',
                'chat'                       => 'chat',
                'documents'                  => 'documents',
                'events-management'          => 'events-management',
                'surat-cuti-mc'              => 'surat-cuti-mc',
                'laporan-masalah-disiplin'   => 'laporan-masalah-disiplin',
                'attendance'                 => 'attendance',
                'attendance-follow-up'       => 'attendance-follow-up',
                'attendance-records'         => 'attendance-records',
                'attendance-monthly-summary' => 'attendance-monthly-summary',
                'attendance-warning-letters' => 'attendance-warning-letters',
                'reports'                    => 'reports',
                'settings'                   => 'settings',
            ];
            $schoolEquivalent = $ipgToSchool[$pageSlug] ?? null;
        @endphp

        @if ($schoolEquivalent)
            <a href="{{ route('school.' . $schoolEquivalent) }}"
               class="block rounded-lg border border-zinc-200 hover:border-emerald-200 bg-zinc-50 hover:bg-emerald-50 px-3 py-3 transition group mb-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 font-semibold mb-1 group-hover:text-emerald-700">School module · wired</div>
                <div class="text-sm font-medium text-zinc-900 group-hover:text-emerald-800 flex items-center gap-1.5">
                    Open school.{{ $schoolEquivalent }}
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </div>
                <div class="text-[11px] text-zinc-500 mt-1">See the wired version, then adapt the data shape into IPG.</div>
            </a>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-3 mb-3">
                <div class="text-[10px] uppercase tracking-[0.14em] text-amber-700 font-semibold mb-1">IPG-only module</div>
                <div class="text-xs text-amber-900">No School equivalent. The data model and UI are designed fresh for IPG context.</div>
            </div>
        @endif

        <div class="text-[11px] text-zinc-400 font-mono tabular-nums leading-relaxed">
            // route<br>
            ipg.{{ $pageSlug }}<br><br>
            // file<br>
            resources/views/ipg/<br>placeholder.blade.php
        </div>
    </x-card>
</div>

@endsection
