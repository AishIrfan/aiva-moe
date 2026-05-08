@extends('layouts.shell')
@section('title', 'School settings')
@section('subtitle', 'thresholds · notifications · integrations')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">School / Settings</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            Tune <span class="text-zinc-400">how alerts and integrations behave.</span>
        </h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

    {{-- Notifications --}}
    <x-card title="Notifications" subtitle="how parents & staff are reached">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[email]" value="1"
                       @checked($settings['notifications']['email'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">Email</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Send alerts and digests via SMTP.</div>
                </div>
            </label>
            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="notifications[sms]" value="1"
                       @checked($settings['notifications']['sms'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">SMS</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Critical alerts only — uses your provider quota.</div>
                </div>
            </label>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- Thresholds --}}
    <x-card title="Thresholds" subtitle="when to escalate">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Late · minutes</label>
                    <input type="number" name="thresholds[late_minutes]"
                           value="{{ $settings['thresholds']['late_minutes'] ?? 15 }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">past this, mark as Late</div>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Crowd warn</label>
                    <input type="number" name="thresholds[crowd_warn]"
                           value="{{ $settings['thresholds']['crowd_warn'] ?? 50 }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">people detected per zone</div>
                </div>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- Class Recording (CLASS_RECORDING_CHECKLIST §10.4) --}}
    <x-card title="Class Recording" subtitle="classroom video — opt-in per school">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <input type="hidden" name="class_recording_form_submitted" value="1"/>

            <div class="px-3 py-2 rounded-md bg-amber-50 border border-amber-200 text-amber-900 text-[11px] leading-relaxed">
                <strong>Compliance reminder:</strong> recording minors in classrooms is subject to PDPA 2010 and school policy.
                Before enabling, confirm: notice posted, parental consent (where required), retention policy aligned, DSAR procedure in place, incident response plan ready.
            </div>

            <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 hover:border-zinc-300 transition cursor-pointer">
                <input type="checkbox" name="class_recording_enabled" value="1"
                       @checked($settings['class_recording']['class_recording_enabled'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
                <div>
                    <div class="font-medium text-zinc-900">Enable Class Recording</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">When enabled, the Class Recording entry appears under Safety &amp; Incidents and uploads are accepted. When disabled, all routes 403 — even direct URL access.</div>
                </div>
            </label>

            <label class="flex items-start gap-3 p-3 rounded-lg border border-rose-200 bg-rose-50/40 hover:border-rose-300 transition cursor-pointer">
                <input type="checkbox" name="class_recording_audio_enabled" value="1"
                       @checked($settings['class_recording']['class_recording_audio_enabled'] ?? false)
                       class="mt-0.5 rounded border-zinc-300 text-rose-600 focus:ring-rose-500"/>
                <div>
                    <div class="font-medium text-zinc-900">Enable audio recording</div>
                    <div class="text-[11px] text-zinc-700 mt-0.5"><strong class="text-rose-700">Higher privacy weight.</strong> Audio captures conversations between students and teachers. Confirm explicit consent and a defensible lawful basis before turning this on. Even when off, v1's manual upload pipeline cannot reliably reject audio-bearing files at the server — uploaders are warned but should self-police.</div>
                </div>
            </label>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Retention (days)</label>
                    <input type="number" name="class_recording_retention_days"
                           min="{{ \App\Models\ClassRecording::RETENTION_MIN_DAYS }}"
                           max="{{ \App\Models\ClassRecording::RETENTION_MAX_DAYS }}"
                           value="{{ $settings['class_recording']['class_recording_retention_days'] ?? \App\Models\ClassRecording::DEFAULT_RETENTION_DAYS }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">{{ \App\Models\ClassRecording::RETENTION_MIN_DAYS }}–{{ \App\Models\ClassRecording::RETENTION_MAX_DAYS }} days; auto-delete after expiry unless preserved</div>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Max file size (MB)</label>
                    <input type="number" name="class_recording_max_file_size_mb" min="1" max="8192"
                           value="{{ $settings['class_recording']['class_recording_max_file_size_mb'] ?? \App\Models\ClassRecording::DEFAULT_MAX_SIZE_MB }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
                    <div class="text-[11px] text-zinc-400 mt-1">app-layer cap; PHP upload_max_filesize still applies</div>
                </div>
            </div>

            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- Retention --}}
    <x-card title="Retention" subtitle="how long camera footage and events are kept">
        <form method="POST" action="{{ route('school.settings.update') }}" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Days</label>
                <input type="number" name="retention[days]"
                       value="{{ $settings['retention']['days'] ?? 30 }}"
                       class="w-32 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono tabular-nums focus:outline-none focus:bg-white focus:border-zinc-300"/>
            </div>
            <button class="inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Save</button>
        </form>
    </x-card>

    {{-- SenseStudio --}}
    <x-card title="SenseStudio" subtitle="face recognition backend">
        <form method="POST" action="{{ route('school.settings.sensestudio.test') }}" class="space-y-2 text-sm">
            @csrf
            <div>
                <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Base URL</label>
                <input name="base_url" placeholder="https://sensestudio.example.com"
                       value="{{ $settings['sensestudio']['base_url'] ?? '' }}"
                       class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 font-mono text-xs focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Username</label>
                    <input name="username"
                           value="{{ $settings['sensestudio']['username'] ?? '' }}"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
                <div>
                    <label class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500 block mb-1">Password</label>
                    <input type="password" name="password"
                           class="w-full bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
                </div>
            </div>
            <button class="inline-flex items-center gap-1.5 bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                Test &amp; save
            </button>
        </form>
    </x-card>
</div>

@endsection
