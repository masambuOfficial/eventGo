<div class="max-w-2xl mx-auto">
    <a href="{{ route('provider.opportunities.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Opportunities</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">{{ $requirement->title }}</h1>
    <p class="text-[14px] text-slate mb-6">{{ $requirement->category->name }} · {{ $requirement->description }}</p>

    @if ($alreadySubmitted)
        <div class="bg-green-100 text-green-900 text-[13px] rounded-sm px-3 py-2 mb-4">
            You've already submitted an offer for this requirement. Changes below will update it.
        </div>
    @endif

    <form wire:submit="submit" class="bg-surface-raised border border-line rounded-lg p-6 space-y-6">
        <div>
            <h2 class="text-[16px] font-semibold text-ink mb-3">Line items</h2>

            @foreach ($items as $index => $item)
                <div wire:key="item-{{ $index }}" class="grid grid-cols-12 gap-2 mb-2 items-start">
                    <div class="col-span-5">
                        <input type="text" wire:model.blur="items.{{ $index }}.description" placeholder="Description"
                               class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div class="col-span-2">
                        <input type="number" step="0.01" wire:model.blur="items.{{ $index }}.quantity" placeholder="Qty"
                               class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div class="col-span-2">
                        <input type="text" wire:model.blur="items.{{ $index }}.unit" placeholder="Unit"
                               class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div class="col-span-2">
                        <input type="number" wire:model.blur="items.{{ $index }}.unit_price_ugx" placeholder="Unit price"
                               class="w-full border border-line rounded-sm px-2 py-1.5 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                    </div>
                    <div class="col-span-1 pt-2">
                        @if (count($items) > 1)
                            <button type="button" wire:click="removeItemRow({{ $index }})" class="text-[13px] text-slate hover:text-amber-700">&times;</button>
                        @endif
                    </div>
                </div>
                @error("items.{$index}.description") <p class="text-[13px] text-amber-700 mb-2">{{ $message }}</p> @enderror
                @error("items.{$index}.unit_price_ugx") <p class="text-[13px] text-amber-700 mb-2">{{ $message }}</p> @enderror
            @endforeach

            <button type="button" wire:click="addItemRow" class="text-[13px] text-green-600 hover:underline">+ Add line item</button>

            <div class="flex items-center justify-between mt-4 pt-4 border-t border-line">
                <span class="text-[14px] font-medium text-ink">Total</span>
                <span class="text-[16px] font-semibold text-ink">UGX {{ number_format($this->totalUgx()) }}</span>
            </div>
        </div>

        <div>
            <label class="block text-[14px] font-medium text-ink mb-1">Scope summary</label>
            <textarea wire:model.blur="scopeSummary" rows="2"
                      class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Inclusions</label>
                <textarea wire:model.blur="inclusions" rows="3"
                          class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Exclusions</label>
                <textarea wire:model.blur="exclusions" rows="3"
                          class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
            </div>
        </div>

        <div>
            <label class="block text-[14px] font-medium text-ink mb-1">Your terms</label>
            <p class="text-[13px] text-slate mb-1">Deposit expectations, cancellation policy, what happens if guest numbers change — this is your agreement, not Event Go's.</p>
            <textarea wire:model.blur="terms" rows="3"
                      class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
            @error('terms') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 items-end">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Offer valid until</label>
                <input type="date" wire:model.blur="validUntil"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('validUntil') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-[14px] text-ink">
                <input type="checkbox" wire:model="availabilityConfirmed" class="rounded-sm border-line">
                I've confirmed my availability for this date
            </label>
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
            <span wire:loading.remove wire:target="submit">{{ $alreadySubmitted ? 'Update offer' : 'Submit offer' }}</span>
            <span wire:loading wire:target="submit">Saving…</span>
        </button>
    </form>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mt-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">Questions &amp; answers</h2>

        @forelse ($clarifications as $clarification)
            <div class="border-t border-line py-3 first:border-t-0">
                <p class="text-[14px] text-ink">{{ $clarification->question }}</p>
                @if ($clarification->answer)
                    <p class="text-[13px] text-slate mt-1">&rarr; {{ $clarification->answer }}</p>
                @else
                    <p class="text-[13px] text-slate mt-1 italic">Awaiting an answer.</p>
                @endif
            </div>
        @empty
            <p class="text-[13px] text-slate">No questions asked yet.</p>
        @endforelse

        <form wire:submit="askQuestion" class="mt-4 flex gap-2">
            <input type="text" wire:model="newQuestion" placeholder="Ask the organiser a question…"
                   class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            <button type="submit" class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-2 transition">Ask</button>
        </form>
        @error('newQuestion') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
    </div>
</div>
