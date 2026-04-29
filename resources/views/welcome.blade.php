<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fafaf9">

    <title>{{ config('app.name', 'AIVA MOE') }} · School operations console</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css">
    @endif
</head>
<body class="font-sans antialiased bg-zinc-50 text-zinc-900 min-h-[100dvh] selection:bg-emerald-100">

    <div class="min-h-[100dvh] flex flex-col max-w-[1320px] mx-auto px-6 lg:px-10">

        {{-- Top bar --}}
        <header class="flex items-center justify-between py-5">
            <a href="/" class="flex items-center gap-2.5">
                <span class="relative inline-flex items-center justify-center w-7 h-7 rounded-md bg-zinc-900 text-emerald-400 font-semibold text-[13px]">A</span>
                <span class="font-semibold tracking-tight">AIVA <span class="text-zinc-400 font-medium">MOE</span></span>
            </a>

            @if (Route::has('login'))
                <nav class="flex items-center gap-1 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-zinc-900 text-white hover:bg-zinc-800 transition">
                            Open dashboard
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-3.5 py-1.5 rounded-md text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 transition">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-zinc-900 text-white hover:bg-zinc-800 transition">
                                Request access
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        {{-- Hero — asymmetric split: 7/12 content, 5/12 console card --}}
        <section class="grid lg:grid-cols-12 gap-10 lg:gap-12 py-10 lg:py-20 flex-1 items-center">

            {{-- Left: pitch --}}
            <div class="lg:col-span-7">
                <div class="inline-flex items-center gap-2 text-[11px] font-medium tracking-wide uppercase text-zinc-500 border border-zinc-200 bg-white rounded-full pl-2 pr-3 py-1 mb-7">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    </span>
                    Live across 1,247 schools
                </div>

                <h1 class="text-[44px] md:text-[60px] lg:text-[68px] leading-[0.96] tracking-tightest font-semibold text-zinc-900">
                    School operations,<br>
                    <span class="text-zinc-400 cursor-blink">surfaced.</span>
                </h1>

                <p class="mt-7 text-lg text-zinc-600 leading-relaxed max-w-[58ch]">
                    Attendance, incidents, camera health, academic signals — every school, on one console.
                    Built for ministry-wide oversight without the spreadsheets.
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-zinc-900 text-white font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
                            Open dashboard
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-zinc-900 text-white font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">
                            Log in to console
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-zinc-200 bg-white text-zinc-900 font-medium hover:border-zinc-300 hover:bg-zinc-50 transition">
                                Request access
                            </a>
                        @endif
                    @endauth

                    <span class="text-xs text-zinc-500 font-mono tabular-nums">
                        v1.0.0 · build {{ now()->format('Ymd') }}
                    </span>
                </div>

                {{-- Trust strip --}}
                <dl class="mt-14 grid grid-cols-3 gap-6 max-w-md border-t border-zinc-200 pt-6">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-zinc-500">Schools</dt>
                        <dd class="font-mono tabular-nums text-2xl font-semibold mt-1">1,247</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-zinc-500">Cameras live</dt>
                        <dd class="font-mono tabular-nums text-2xl font-semibold mt-1">8,431<span class="text-zinc-400 text-base">/8,612</span></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-zinc-500">Attendance · 7d</dt>
                        <dd class="font-mono tabular-nums text-2xl font-semibold mt-1 text-emerald-700">94.3%</dd>
                    </div>
                </dl>
            </div>

            {{-- Right: console snapshot --}}
            <div class="lg:col-span-5 lg:pl-4">
                <div class="relative rounded-2xl border border-zinc-200 bg-white shadow-pop overflow-hidden float-y">

                    {{-- Window chrome --}}
                    <div class="flex items-center gap-1.5 px-4 h-9 border-b border-zinc-100 bg-zinc-50/60">
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-300"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-300"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-zinc-300"></span>
                        <span class="ml-3 text-[11px] font-mono text-zinc-500 tracking-wide">aiva.moe / overview</span>
                        <span class="ml-auto inline-flex items-center gap-1.5 text-[10px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200/70 rounded px-1.5 py-0.5">
                            <span class="relative flex h-1 w-1">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                                <span class="relative inline-flex h-1 w-1 rounded-full bg-emerald-500"></span>
                            </span>
                            Live
                        </span>
                    </div>

                    {{-- Mock content --}}
                    <div class="p-5 space-y-4">
                        <div>
                            <div class="text-[10px] uppercase tracking-[0.14em] text-zinc-400 mb-1.5">Active alerts · last 24h</div>
                            <div class="flex items-baseline gap-3">
                                <span class="font-mono tabular-nums text-3xl font-semibold tracking-tight">47</span>
                                <span class="text-xs text-emerald-700 font-medium">−12.4% vs yesterday</span>
                            </div>
                        </div>

                        {{-- Sparkline --}}
                        <svg class="w-full h-14" viewBox="0 0 320 56" preserveAspectRatio="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="sparkfill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%"   stop-color="rgb(16,185,129)" stop-opacity="0.18"/>
                                    <stop offset="100%" stop-color="rgb(16,185,129)" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path d="M0,40 L20,38 L40,42 L60,30 L80,34 L100,22 L120,28 L140,18 L160,24 L180,14 L200,20 L220,12 L240,18 L260,8 L280,16 L300,10 L320,18 L320,56 L0,56 Z"
                                  fill="url(#sparkfill)"/>
                            <path d="M0,40 L20,38 L40,42 L60,30 L80,34 L100,22 L120,28 L140,18 L160,24 L180,14 L200,20 L220,12 L240,18 L260,8 L280,16 L300,10 L320,18"
                                  fill="none" stroke="rgb(16,185,129)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>

                        {{-- Mini list --}}
                        <ul class="divide-y divide-zinc-100 -mx-1 text-sm">
                            @foreach ([
                                ['SK Bukit Bintang',        'Camera offline · gate 2', 'bg-rose-500'],
                                ['SMK Damansara Jaya',      'Late arrivals · 18',      'bg-amber-500'],
                                ['SK Taman Tun Dr Ismail',  'Attendance ↑ 2.1%',       'bg-emerald-500'],
                                ['SMK Section 17',          'Discipline report filed', 'bg-zinc-400'],
                            ] as $row)
                                <li class="flex items-center gap-3 px-1 py-2">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $row[2] }}"></span>
                                    <span class="text-zinc-900 font-medium truncate">{{ $row[0] }}</span>
                                    <span class="ml-auto text-xs text-zinc-500 truncate">{{ $row[1] }}</span>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Streaming-data progress bar (Tessera-style) --}}
                        <div class="shimmer-bar h-0.5 mt-1"></div>
                    </div>
                </div>

                {{-- Tiny footnote --}}
                <p class="mt-3 text-[11px] text-zinc-400 font-mono tracking-wide pl-1">
                    /// snapshot · refreshed {{ now()->format('H:i') }} MYT
                </p>
            </div>
        </section>

        <footer class="border-t border-zinc-200 py-5 text-xs text-zinc-500 flex flex-wrap items-center justify-between gap-3">
            <span>© {{ now()->year }} {{ config('app.name') }} — Ministry of Education console</span>
            <span class="font-mono tabular-nums">Laravel {{ \Illuminate\Foundation\Application::VERSION }} · PHP {{ PHP_VERSION }}</span>
        </footer>
    </div>
</body>
</html>
