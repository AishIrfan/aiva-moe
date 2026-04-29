@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-emerald-500 text-start text-sm font-medium text-emerald-700 bg-emerald-50/60 transition'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-sm font-medium text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 hover:border-zinc-300 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
