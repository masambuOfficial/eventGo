<x-layouts.guest title="Log in">
    <h1 class="text-[24px] font-semibold text-ink mb-6">Log in</h1>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-sm px-3 py-2">
            {{ session('status') }}
        </div>
    @endif

    <a href="{{ route('auth.google.redirect') }}"
       class="w-full flex items-center justify-center gap-2 border border-line rounded-sm px-4 py-2 text-sm font-medium text-ink hover:bg-surface transition mb-4">
        Continue with Google
    </a>

    <div class="flex items-center gap-3 my-4">
        <div class="h-px flex-1 bg-line"></div>
        <span class="text-[13px] text-slate">or with email</span>
        <div class="h-px flex-1 bg-line"></div>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[14px] font-medium text-ink mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('email')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-[13px] text-slate">
                <input type="checkbox" name="remember" class="rounded-sm border-line">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-[13px] text-green-600 hover:underline">Forgot password?</a>
        </div>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Log in
        </button>
    </form>

    <p class="mt-6 text-center text-[13px] text-slate">
        New to Event Go?
        <a href="{{ route('register') }}" class="text-green-600 hover:underline">Create an account</a>
    </p>
</x-layouts.guest>
