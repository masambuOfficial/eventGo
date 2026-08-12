<x-layouts.app title="Admin">
    <h1 class="text-[24px] font-semibold text-ink mb-2">Admin</h1>
    <p class="text-[14px] text-slate mb-4">
        Taxonomy management and user administration land here as later phases build out (architecture §7).
    </p>
    <a href="{{ route('admin.verifications.index') }}" class="text-[14px] text-green-600 hover:underline">
        Verification queue
    </a>
</x-layouts.app>
