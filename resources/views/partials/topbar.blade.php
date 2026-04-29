@php
    $selectedSchool = session('school_name');
    $user = auth()->user();
@endphp
<header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-zinc-200">
    <div class="h-14 flex items-center gap-3 px-4 sm:px-6">

        {{-- Sidebar toggle (mobile) --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 -ml-2 rounded-md text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 transition"
                aria-label="Toggle sidebar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        {{-- Page heading --}}
        <div class="min-w-0 flex-1 lg:flex-initial">
            <div class="text-[15px] font-semibold tracking-tight text-zinc-900 truncate">
                @yield('title', 'Dashboard')
            </div>
            <div class="text-xs text-zinc-500 truncate -mt-0.5">
                @yield('subtitle', $selectedSchool ?: ' ')
            </div>
        </div>

        {{-- Search --}}
        <form action="{{ route('search') }}" method="GET" class="ml-auto hidden md:block">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400 pointer-events-none"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" placeholder="Search students, events, schools…"
                       value="{{ request('q') }}"
                       class="w-72 lg:w-80 bg-zinc-50 border border-zinc-200 rounded-lg pl-9 pr-16 py-1.5 text-sm
                              focus:bg-white focus:border-zinc-300 focus:outline-none transition placeholder:text-zinc-400"/>
                <kbd class="hidden lg:flex absolute right-2 top-1/2 -translate-y-1/2 items-center gap-0.5 px-1.5 py-0.5 rounded
                            bg-white border border-zinc-200 text-[10px] font-mono text-zinc-500">
                    /
                </kbd>
            </div>
        </form>

        <div class="flex items-center gap-1 ml-auto md:ml-2">

            {{-- Health pill --}}
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-medium
                        text-emerald-700 bg-emerald-50 border border-emerald-200/70">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                </span>
                Healthy
            </div>

            {{-- Alerts (school mode) --}}
            @if (session('mode') !== 'moe')
                <a href="{{ route('school.alerts') }}"
                   class="relative p-2 rounded-md text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 transition"
                   title="Alerts">
                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 9a6 6 0 0 1 12 0c0 6 3 7 3 7H3s3-1 3-7Z"/>
                        <path d="M10 21a2 2 0 0 0 4 0"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                </a>
            @endif

            {{-- User menu --}}
            @auth
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 rounded-md hover:bg-zinc-100 px-1.5 py-1 transition"
                            :class="open && 'bg-zinc-100'">
                        <span class="w-7 h-7 rounded-full bg-zinc-900 text-white flex items-center justify-center text-[11px] font-semibold tracking-wide">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:flex flex-col text-left leading-tight pr-1">
                            <span class="text-[12px] font-medium text-zinc-900 truncate max-w-[140px]">{{ $user->name }}</span>
                            <span class="text-[10px] text-zinc-500 -mt-0.5 truncate max-w-[140px]">{{ $user->email }}</span>
                        </span>
                        <svg class="hidden sm:block w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.outside="open=false" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-2 w-56 bg-white border border-zinc-200 rounded-xl shadow-pop overflow-hidden text-sm">

                        <div class="px-3 py-2.5 border-b border-zinc-100">
                            <div class="text-zinc-900 font-medium truncate">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}"
                               class="flex items-center gap-2.5 px-3 py-1.5 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900">
                                <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>
                                </svg>
                                Profile
                            </a>
                            @if (session('mode') !== 'moe')
                                <a href="{{ route('school.settings') }}"
                                   class="flex items-center gap-2.5 px-3 py-1.5 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900">
                                    <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M19 12a7 7 0 0 0-.1-1.4l2-1.5-2-3.5-2.4.8a7 7 0 0 0-2.4-1.4L13.5 2h-3l-.6 2.4a7 7 0 0 0-2.4 1.4l-2.4-.8-2 3.5 2 1.5A7 7 0 0 0 5 12c0 .5 0 1 .1 1.4l-2 1.5 2 3.5 2.4-.8a7 7 0 0 0 2.4 1.4l.6 2.4h3l.6-2.4a7 7 0 0 0 2.4-1.4l2.4.8 2-3.5-2-1.5c.1-.5.1-.9.1-1.4Z"/>
                                    </svg>
                                    School settings
                                </a>
                            @endif
                            @if ($user->canSwitchMode())
                                <a href="{{ route('moe.schools') }}"
                                   class="flex items-center gap-2.5 px-3 py-1.5 text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900">
                                    <svg class="w-4 h-4 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 21h18M5 21V11M9 21V11M15 21V11M19 21V11"/><path d="m12 3-9 5h18l-9-5Z"/>
                                    </svg>
                                    MOE · Schools
                                </a>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-zinc-100">
                            @csrf
                            <button class="flex w-full items-center gap-2.5 px-3 py-2 text-rose-600 hover:bg-rose-50">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
                                </svg>
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>
