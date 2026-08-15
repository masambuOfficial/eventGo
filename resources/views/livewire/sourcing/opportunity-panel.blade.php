<div class="max-w-2xl mx-auto">
    <a href="{{ route('events.dashboard', $requirement->event) }}" class="text-[13px] text-slate hover:text-ink">&larr; {{ $requirement->event->name }}</a>

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">{{ $requirement->title }}</h1>
            <p class="text-[14px] text-slate">
                {{ $requirement->category->name }} ·
                Status: {{ ucfirst(str_replace('_', ' ', (string) $requirement->status)) }}
            </p>
        </div>
        <a href="{{ route('sourcing.offers', $requirement) }}" class="text-[13px] text-green-600 hover:underline whitespace-nowrap">
            Compare offers &rarr;
        </a>
    </div>

    <div class="bg-white border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Publish as an open opportunity</h2>

        @if ($opportunity && $opportunity->status === 'open')
            <p class="text-[14px] text-ink mb-3">
                Published {{ $opportunity->published_at?->diffForHumans() }} ·
                {{ $opportunity->view_count }} views · {{ $opportunity->offer_count }} offers
            </p>
            <button type="button" wire:click="close" wire:loading.attr="disabled" wire:target="close"
                    class="text-[13px] text-slate hover:text-ink underline">
                Close to new offers
            </button>
        @else
            <form wire:submit="publish" class="space-y-4">
                <div>
                    <label class="block text-[14px] font-medium text-ink mb-1">Closes on</label>
                    <input type="date" wire:model="closesAt"
                           class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    @error('closesAt') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-[14px] text-ink">
                    <input type="checkbox" wire:model="budgetVisible" class="rounded-sm border-line">
                    Show my budget range to providers
                </label>

                @if ($budgetVisible)
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[14px] font-medium text-ink mb-1">Budget from (UGX)</label>
                            <input type="number" wire:model="budgetMinUgx"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                            @error('budgetMinUgx') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[14px] font-medium text-ink mb-1">Budget to (UGX)</label>
                            <input type="number" wire:model="budgetMaxUgx"
                                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                            @error('budgetMaxUgx') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                <button type="submit" wire:loading.attr="disabled" wire:target="publish"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="publish">Publish opportunity</span>
                    <span wire:loading wire:target="publish">Publishing…</span>
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Invite providers directly</h2>

        <textarea wire:model="inviteMessage" rows="2" placeholder="Optional note to include with the invitation…"
                  class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink mb-4 focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>

        @forelse ($candidates as $match)
            <div wire:key="candidate-{{ $match['provider']->id }}" class="flex items-center justify-between border-t border-line py-3 first:border-t-0">
                <div>
                    <p class="text-[14px] font-medium text-ink">{{ $match['provider']->business_name }}</p>
                    <p class="text-[13px] text-slate">Match score {{ number_format($match['score'] * 100, 0) }}%</p>
                </div>
                <button type="button" wire:click="invite({{ $match['provider']->id }})"
                        wire:loading.attr="disabled" wire:target="invite({{ $match['provider']->id }})"
                        class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-1.5 transition disabled:opacity-60">
                    Invite
                </button>
            </div>
        @empty
            <p class="text-[13px] text-slate">No matching providers found for this category and district yet.</p>
        @endforelse
    </div>

    @if ($invitations->isNotEmpty())
        <div class="bg-white border border-line rounded-lg p-6">
            <h2 class="text-[16px] font-semibold text-ink mb-4">Invitations sent</h2>

            @foreach ($invitations as $invitation)
                <div class="flex items-center justify-between border-t border-line py-3 first:border-t-0">
                    <p class="text-[14px] text-ink">{{ $invitation->provider->business_name }}</p>
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full bg-surface text-slate">
                        {{ ucfirst($invitation->status) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
