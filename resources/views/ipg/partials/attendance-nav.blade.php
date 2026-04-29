@php
    $pages = [
        ['Today',           'ipg.attendance'],
        ['Follow-up',       'ipg.attendance-follow-up'],
        ['Records',         'ipg.attendance-records'],
        ['Monthly summary', 'ipg.attendance-monthly-summary'],
        ['Warning letters', 'ipg.attendance-warning-letters'],
    ];
@endphp

<nav class="flex flex-wrap gap-1 mb-3 text-xs">
    @foreach ($pages as $p)
        <a href="{{ route($p[1]) }}"
           class="px-2.5 py-1 rounded-md border transition
                  {{ request()->routeIs($p[1])
                        ? 'border-zinc-900 bg-zinc-900 text-white'
                        : 'border-zinc-200 bg-white text-zinc-600 hover:text-zinc-900 hover:border-zinc-300' }}">{{ $p[0] }}</a>
    @endforeach
</nav>
