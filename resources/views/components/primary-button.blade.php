<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-zinc-900 text-white rounded-lg text-sm font-medium hover:bg-zinc-800 active:translate-y-[1px] focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
