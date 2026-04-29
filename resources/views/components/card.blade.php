@props(['title' => null, 'subtitle' => null, 'eyebrow' => null, 'action' => null, 'padding' => 'p-5'])

<div {{ $attributes->merge([
    'class' => 'bg-white border border-zinc-200 rounded-xl shadow-card ' . $padding
]) }}>
    @if ($title || $eyebrow || $action)
        <div class="flex items-start justify-between gap-3 {{ $slot->isEmpty() ? '' : 'mb-4' }}">
            <div class="min-w-0">
                @if ($eyebrow)
                    <div class="text-[10px] uppercase tracking-[0.14em] font-semibold text-zinc-400 mb-1">{{ $eyebrow }}</div>
                @endif
                @if ($title)
                    <div class="text-[13px] font-semibold tracking-tight text-zinc-900 truncate">{{ $title }}</div>
                @endif
                @if ($subtitle)
                    <div class="text-xs text-zinc-500 mt-0.5 truncate">{{ $subtitle }}</div>
                @endif
            </div>
            @if ($action)
                <div class="shrink-0 text-xs">{{ $action }}</div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
