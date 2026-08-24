<x-layouts.guest title="Confirm password">
    <h1 class="text-[24px] font-semibold text-ink mb-2">Confirm your password</h1>
    <p class="text-[14px] text-slate mb-6">This is a sensitive area — please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
        @csrf

        <x-password-field autofocus autocomplete="current-password" />

        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Confirm
        </button>
    </form>
</x-layouts.guest>
