<x-layouts.guest title="Create your account">
    <h1 class="text-[24px] font-semibold text-ink mb-6">
        @if (($intent ?? null) === 'organiser')
            Create your account to plan your event
        @elseif (($intent ?? null) === 'provider')
            Create your account to start offering your services
        @else
            Create your account
        @endif
    </h1>

    <a href="{{ route('auth.google.redirect') }}"
       class="w-full flex items-center justify-center gap-2 border border-line rounded-sm px-4 py-2 text-sm font-medium text-ink hover:bg-surface transition mb-4">
        Continue with Google
    </a>

    <div class="flex items-center gap-3 my-4">
        <div class="h-px flex-1 bg-line"></div>
        <span class="text-[13px] text-slate">or with email</span>
        <div class="h-px flex-1 bg-line"></div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="full_name" class="block text-[14px] font-medium text-ink mb-1">Full name</label>
            <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('full_name')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-[14px] font-medium text-ink mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('email')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field autocomplete="new-password" />

        <x-password-field name="password_confirmation" label="Confirm password" autocomplete="new-password" />

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-[13px] text-slate">
        Already have an account?
        <a href="{{ route('login') }}" class="text-green-600 hover:underline">Log in</a>
    </p>
</x-layouts.guest>
