<div class="max-w-3xl mx-auto">
    <a href="{{ route('sourcing.show', $requirement) }}" class="text-[13px] text-slate hover:text-ink">&larr; {{ $requirement->title }}</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Compare offers</h1>

    @forelse ($offers as $offer)
        <div wire:key="offer-{{ $offer->id }}" x-data="{ open: false }" class="bg-white border border-line rounded-lg p-5 mb-4">
            <button type="button" @click="open = ! open" class="w-full text-left">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[16px] font-semibold text-ink">{{ $offer->provider->business_name }}</p>
                        <p class="text-[13px] text-slate">{{ $offer->scope_summary }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[18px] font-semibold text-ink">UGX {{ number_format($offer->total_ugx) }}</p>
                        <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $shortlistedOfferIds->contains($offer->id) ? 'bg-green-100 text-green-900' : 'bg-surface text-slate' }}">
                            {{ ucfirst(str_replace('_', ' ', (string) $offer->status)) }}
                        </span>
                    </div>
                </div>
            </button>

            <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-line space-y-4">
                @if ($offer->items->isNotEmpty())
                    <table class="w-full text-[13px]">
                        <thead>
                            <tr class="text-left text-slate">
                                <th class="pb-2 font-medium">Item</th>
                                <th class="pb-2 font-medium text-right">Qty</th>
                                <th class="pb-2 font-medium text-right">Unit price</th>
                                <th class="pb-2 font-medium text-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($offer->items as $item)
                                <tr class="border-t border-line">
                                    <td class="py-1.5 text-ink">{{ $item->description }}</td>
                                    <td class="py-1.5 text-right text-ink">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} {{ $item->unit }}</td>
                                    <td class="py-1.5 text-right text-ink">{{ number_format($item->unit_price_ugx) }}</td>
                                    <td class="py-1.5 text-right text-ink">{{ number_format($item->line_total_ugx) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if ($offer->inclusions)
                    <p class="text-[13px] text-ink"><span class="font-medium">Includes:</span> {{ $offer->inclusions }}</p>
                @endif
                @if ($offer->exclusions)
                    <p class="text-[13px] text-ink"><span class="font-medium">Excludes:</span> {{ $offer->exclusions }}</p>
                @endif
                @if ($offer->terms)
                    <p class="text-[13px] text-ink bg-surface rounded-sm p-3"><span class="font-medium">Provider's terms:</span> {{ $offer->terms }}</p>
                @endif
                <p class="text-[13px] text-slate">
                    {{ $offer->availability_confirmed ? 'Availability confirmed' : 'Availability not yet confirmed' }}
                    @if ($offer->valid_until) · Valid until {{ $offer->valid_until->format('j M Y') }} @endif
                </p>

                @if (in_array((string) $offer->status, ['submitted', 'under_review'], true))
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="shortlist({{ $offer->id }})"
                                class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-1.5 transition">
                            Shortlist
                        </button>
                        <button type="button" wire:click="reject({{ $offer->id }})" wire:confirm="Reject this offer?"
                                class="text-[13px] text-slate hover:text-amber-700">
                            Reject
                        </button>
                    </div>
                @elseif ((string) $offer->status === 'shortlisted')
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="accept({{ $offer->id }})"
                                wire:confirm="Accept this offer? Contact details will be released immediately and every other offer on this requirement will be rejected."
                                wire:loading.attr="disabled" wire:target="accept({{ $offer->id }})"
                                class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-1.5 transition disabled:opacity-60">
                            Accept &amp; book
                        </button>
                        <button type="button" wire:click="reject({{ $offer->id }})" wire:confirm="Reject this offer?"
                                class="text-[13px] text-slate hover:text-amber-700">
                            Reject
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white border border-line rounded-lg p-6 text-center">
            <p class="text-[14px] text-slate">No offers submitted yet.</p>
        </div>
    @endforelse

    @if ($clarifications->isNotEmpty())
        <div class="bg-white border border-line rounded-lg p-6 mt-6">
            <h2 class="text-[16px] font-semibold text-ink mb-4">Questions from providers</h2>

            @foreach ($clarifications as $clarification)
                <div class="border-t border-line py-3 first:border-t-0">
                    <p class="text-[14px] text-ink">{{ $clarification->question }}</p>
                    @if ($clarification->answer)
                        <p class="text-[13px] text-slate mt-1">&rarr; {{ $clarification->answer }}</p>
                    @else
                        <form wire:submit="answer({{ $clarification->id }})" class="mt-2 flex gap-2">
                            <input type="text" wire:model="answers.{{ $clarification->id }}" placeholder="Type your answer…"
                                   class="flex-1 border border-line rounded-sm px-3 py-1.5 text-[13px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                            <button type="submit" class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-1.5 transition">Answer</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
