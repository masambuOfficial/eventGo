<x-layouts.guest title="Set a new password">
    <h1 class="text-[24px] font-semibold text-ink mb-6">Set a new password</h1>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-[14px] font-medium text-ink mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            @error('email')
                <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p>
            @enderror
        </div>

        <x-password-field label="New password" autocomplete="new-password" />

        <x-password-field name="password_confirmation" label="Confirm new password" autocomplete="new-password" />

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Reset password
        </button>
    </form>
</x-layouts.guest>
