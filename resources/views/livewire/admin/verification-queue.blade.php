<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back to admin</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Verification queue</h1>

    @forelse ($pending as $verification)
        <div wire:key="verif-{{ $verification->id }}" class="bg-white border border-line rounded-lg p-6 mb-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[16px] font-medium text-ink">{{ $verification->provider->business_name }}</p>
                    <p class="text-[13px] text-slate">Tier {{ $verification->tier }} — {{ str_replace('_', ' ', $verification->evidence_type) }}</p>
                </div>
                <span class="text-[13px] text-slate">{{ $verification->created_at->diffForHumans() }}</span>
            </div>

            @if ($verification->evidence_path)
                <a href="{{ $verification->evidence_path }}" target="_blank" rel="noopener"
                   class="block mt-2 text-[13px] text-green-600 hover:underline">{{ $verification->evidence_path }}</a>
            @endif

            @if ($verification->notes)
                <p class="mt-2 text-[14px] text-slate">{{ $verification->notes }}</p>
            @endif

            <div class="flex items-center gap-3 mt-4">
                <button wire:click="approve({{ $verification->id }})" wire:confirm="Approve this verification?"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-1.5 text-[14px] font-medium transition">
                    Approve
                </button>
                <button wire:click="reject({{ $verification->id }})" wire:confirm="Reject this verification?"
                        class="text-[14px] text-amber-700 hover:underline">
                    Reject
                </button>
            </div>
        </div>
    @empty
        <p class="text-[14px] text-slate">Nothing waiting for review.</p>
    @endforelse
</div>
