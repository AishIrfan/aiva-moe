<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Theme color swaps for the login page to match the dark video overlay --}}
    <meta name="theme-color" content="{{ request()->routeIs('login') ? '#0a0a0a' : '#fafaf9' }}">

    <title>{{ config('app.name', 'AIVA MOE') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- Body background flips dark for login (video underneath); stays light for register / password reset / etc. --}}
<body class="font-sans antialiased {{ request()->routeIs('login') ? 'bg-zinc-950 text-zinc-100' : 'bg-zinc-50 text-zinc-900' }} min-h-[100dvh] selection:bg-emerald-100">

    @if (request()->routeIs('login'))
        {{--
            Login-only video background per LOGIN_VIDEO_BACKGROUND_CHECKLIST.md.
            Card, form, wordmark stay structurally unchanged — colours are
            lifted to light variants for legibility against the dark overlay.

            Asset files (NOT in git — must be sourced + encoded externally):
              public/login-background/bg-desktop.webm   (VP9, 1920x1080, ~1.5 Mbps)
              public/login-background/bg-desktop.mp4    (H.264, 1920x1080, ~1.5 Mbps, +faststart)
              public/login-background/bg-mobile.mp4     (H.264, 1280x720, ~800 Kbps, +faststart)
              public/login-background/bg-poster.jpg     (1920x1080, q=80, ≤250 KB)

            See public/login-background/README.md for sourcing + ffmpeg recipes.
        --}}
        <div class="login-bg fixed inset-0 -z-10 bg-zinc-950 bg-cover bg-center"
             aria-hidden="true"
             style="background-image: url('{{ asset('login-background/bg-poster.jpg') }}');">
            {{-- preload=metadata: only fetch headers up-front, not the whole file --}}
            {{-- autoplay+muted+loop+playsinline: the magic combo browsers (incl. iOS) require for autoplay --}}
            {{-- motion-reduce:hidden: video disappears under prefers-reduced-motion; poster stays via CSS bg above --}}
            <video class="login-bg-video w-full h-full object-cover motion-reduce:hidden"
                   autoplay muted loop playsinline preload="metadata"
                   poster="{{ asset('login-background/bg-poster.jpg') }}"
                   aria-hidden="true">
                <source src="{{ asset('login-background/bg-desktop.webm') }}" type="video/webm" media="(min-width: 768px)">
                <source src="{{ asset('login-background/bg-desktop.mp4') }}"  type="video/mp4"  media="(min-width: 768px)">
                <source src="{{ asset('login-background/bg-mobile.mp4') }}"   type="video/mp4">
            </video>
            {{-- Dark gradient overlay: keeps the white login card crisp against bright video frames --}}
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/60"></div>
        </div>
    @endif

    <div class="relative z-10 min-h-[100dvh] flex flex-col items-center justify-center px-4 py-10">

        <a href="/" class="flex items-center gap-2.5 mb-6 group">
            {{-- Wordmark inverts to light on the login page so it stays legible against the video overlay --}}
            <span class="inline-flex w-9 h-9 transition {{ request()->routeIs('login') ? 'text-white group-hover:text-zinc-200' : 'text-zinc-900 group-hover:text-zinc-800' }}">
                <x-application-logo class="w-9 h-9" />
            </span>
            <span class="font-semibold tracking-tight text-lg {{ request()->routeIs('login') ? 'text-white' : 'text-zinc-900' }}">
                AIVA <span class="font-medium {{ request()->routeIs('login') ? 'text-white/60' : 'text-zinc-400' }}">MOE</span>
            </span>
        </a>

        {{--
            Card: opaque white + zinc border on register/password-reset (clean
            paper look against a flat zinc-50 body). On /login it switches to
            a liquid-glass surface — translucent white tint, heavy backdrop
            blur, soft inner highlight ring, deep drop shadow — so the
            classroom video shows through softly while form text stays crisp.
            Structure / padding / rounded corners are identical to the
            non-login version.
        --}}
        <div class="w-full max-w-md rounded-2xl overflow-hidden
                    {{ request()->routeIs('login')
                        ? 'bg-white/10 backdrop-blur-md border border-white/25 shadow-2xl shadow-black/40 ring-1 ring-inset ring-white/15'
                        : 'bg-white border border-zinc-200 shadow-pop' }}">
            <div class="p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-5 text-[11px] font-mono tabular-nums {{ request()->routeIs('login') ? 'text-white/50' : 'text-zinc-400' }}">
            v1.0.0 · {{ now()->format('Y') }}
        </p>
    </div>

    @if (request()->routeIs('login'))
        {{--
            Pause the background video when the tab is hidden — saves battery on
            mobile and respects user attention. The .catch() swallows the autoplay-
            blocked promise rejection silently; if .play() fails the poster remains.
        --}}
        <script>
            (function () {
                if (!('hidden' in document)) return;
                document.addEventListener('visibilitychange', function () {
                    var v = document.querySelector('.login-bg-video');
                    if (!v) return;
                    if (document.hidden) {
                        v.pause();
                    } else {
                        var p = v.play();
                        if (p && typeof p.catch === 'function') p.catch(function () {});
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
