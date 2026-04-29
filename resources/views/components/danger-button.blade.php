<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 text-white rounded-lg text-sm font-medium hover:bg-rose-700 active:translate-y-[1px] focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition']) }}>
    {{ $slot }}
</button>
