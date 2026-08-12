<x-layouts.guest title="Verify your email">
    <h1 class="text-[24px] font-semibold text-ink mb-2">Verify your email</h1>
    <p class="text-[14px] text-slate mb-6">
        We sent a verification link to {{ auth()->user()->email }}. Follow it to activate your account.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-sm px-3 py-2">
            A new verification link has been sent.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Resend verification email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full text-center text-[13px] text-slate hover:underline">
            Log out
        </button>
    </form>
</x-layouts.guest>
