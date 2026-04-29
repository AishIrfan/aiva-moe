<x-guest-layout>
    <div class="mb-6">
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-1.5">Verify email</div>
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900">Check your inbox</h1>
        <p class="text-sm text-zinc-500 mt-1">We sent a verification link. Click it to finish setting up your account.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 flex items-start gap-2">
            <svg class="w-4 h-4 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span>A new verification link has been sent to your email.</span>
        </div>
    @endif

    <div class="flex items-center justify-between gap-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Resend link
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-xs text-zinc-500 hover:text-zinc-900 underline underline-offset-4">
                Log out
            </button>
        </form>
    </div>
</x-guest-layout>
