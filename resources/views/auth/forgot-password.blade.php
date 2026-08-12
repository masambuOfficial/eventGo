<x-layouts.guest title="Reset your password">
    <h1 class="text-[24px] font-semibold text-ink mb-2">Reset your password</h1>
    <p class="text-[14px] text-slate mb-6">Enter your email and we'll send you a link to reset it.</p>

    @if (session('status'))
        <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-sm px-3 py-2">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-[14px] font-medium text-ink mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('email')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Send reset link
        </button>
    </form>

    <p class="mt-6 text-center text-[13px] text-slate">
        <a href="{{ route('login') }}" class="text-green-600 hover:underline">Back to log in</a>
    </p>
</x-layouts.guest>
