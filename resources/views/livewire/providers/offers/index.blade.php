<div class="max-w-2xl mx-auto">
    <h1 class="text-[24px] font-semibold text-ink mb-1">Your offers</h1>
    <p class="text-[14px] text-slate mb-6">Everything you've submitted, across every requirement.</p>

    @php
        $statusStyles = [
            'draft' => 'bg-surface text-slate', 'submitted' => 'bg-amber-100 text-amber-700',
            'under_review' => 'bg-amber-100 text-amber-700', 'shortlisted' => 'bg-green-100 text-green-900',
            'accepted' => 'bg-green-600 text-white', 'rejected' => 'bg-surface text-slate',
            'withdrawn' => 'bg-surface text-slate', 'expired' => 'bg-surface text-slate',
        ];
    @endphp

    @forelse ($offers as $offer)
        <div class="bg-surface-raised border border-line rounded-lg p-4 mb-3 flex items-center justify-between">
            <div>
                <p class="text-[14px] font-medium text-ink">{{ $offer->requirement->title }}</p>
                <p class="text-[13px] text-slate">
                    {{ $offer->requirement->category->name }} · UGX {{ number_format($offer->total_ugx) }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $statusStyles[(string) $offer->status] ?? 'bg-surface text-slate' }}">
                    {{ ucfirst(str_replace('_', ' ', (string) $offer->status)) }}
                </span>
                @if ((string) $offer->status === 'submitted')
                    <button type="button" wire:click="withdraw({{ $offer->id }})" wire:confirm="Withdraw this offer?"
                            class="text-[13px] text-slate hover:text-amber-700">Withdraw</button>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-surface-raised border border-line rounded-lg p-6 text-center">
            <p class="text-[14px] text-slate">You haven't submitted any offers yet.</p>
        </div>
    @endforelse
</div>
