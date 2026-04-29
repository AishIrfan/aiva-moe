<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#fafaf9">

    <title>{{ config('app.name', 'AIVA MOE') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700|geist-mono:400,500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-zinc-50 text-zinc-900 min-h-[100dvh] selection:bg-emerald-100">

    <div class="min-h-[100dvh] flex flex-col items-center justify-center px-4 py-10">

        <a href="/" class="flex items-center gap-2.5 mb-6 group">
            <span class="inline-flex w-9 h-9 text-zinc-900 group-hover:text-zinc-800 transition">
                <x-application-logo class="w-9 h-9" />
            </span>
            <span class="font-semibold tracking-tight text-lg">AIVA <span class="text-zinc-400 font-medium">MOE</span></span>
        </a>

        <div class="w-full max-w-md bg-white border border-zinc-200 rounded-2xl shadow-pop overflow-hidden">
            <div class="p-6 sm:p-8">
                {{ $slot }}
            </div>
        </div>

        <p class="mt-5 text-[11px] text-zinc-400 font-mono tabular-nums">
            v1.0.0 · {{ now()->format('Y') }}
        </p>
    </div>
</body>
</html>
