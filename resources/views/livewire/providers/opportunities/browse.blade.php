<div class="max-w-2xl mx-auto">
    <h1 class="text-[24px] font-semibold text-ink mb-1">Opportunities</h1>
    <p class="text-[14px] text-slate mb-6">Open requirements matching your services and areas.</p>

    @forelse ($opportunities as $opportunity)
        <div wire:key="opp-{{ $opportunity->id }}" x-data="{ open: false }" class="bg-surface-raised border border-line rounded-lg p-4 mb-3">
            <button type="button" @click="open = ! open; $wire.view({{ $opportunity->id }})" class="w-full text-left">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[14px] font-medium text-ink">{{ $opportunity->requirement->title }}</p>
                        <p class="text-[13px] text-slate">
                            {{ $opportunity->requirement->category->name }} ·
                            {{ $opportunity->requirement->event->district?->name }}
                        </p>
                    </div>
                    @if ($opportunity->budget_visible && $opportunity->budget_min_ugx)
                        <span class="text-[13px] text-ink">
                            UGX {{ number_format($opportunity->budget_min_ugx) }}–{{ number_format($opportunity->budget_max_ugx) }}
                        </span>
                    @endif
                </div>
            </button>

            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-line text-[13px] text-slate">
                <p>{{ $opportunity->requirement->description ?: 'No further description provided.' }}</p>
                @if ($opportunity->closes_at)
                    <p class="mt-2">Closes {{ $opportunity->closes_at->format('j M Y') }}</p>
                @endif
                <a href="{{ route('offers.submit', $opportunity->requirement) }}" class="inline-block mt-3 text-[13px] text-green-600 hover:underline">
                    Submit an offer &rarr;
                </a>
            </div>
        </div>
    @empty
        <div class="bg-surface-raised border border-line rounded-lg p-6 text-center">
            <p class="text-[14px] text-slate">No open opportunities match your services and areas right now.</p>
        </div>
    @endforelse
</div>
