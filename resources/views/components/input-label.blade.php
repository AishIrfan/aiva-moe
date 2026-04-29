@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-500']) }}>
    {{ $value ?? $slot }}
</label>
