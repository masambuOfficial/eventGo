<div class="max-w-2xl mx-auto">
    <h1 class="text-[24px] font-semibold text-ink mb-1">Invitations</h1>
    <p class="text-[14px] text-slate mb-6">Organisers who invited you directly.</p>

    @forelse ($invitations as $invitation)
        <div wire:key="inv-{{ $invitation->id }}" x-data="{ open: false }" class="bg-white border border-line rounded-lg p-4 mb-3">
            <button type="button" @click="open = ! open; $wire.view({{ $invitation->id }})" class="w-full text-left">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[14px] font-medium text-ink">{{ $invitation->requirement->title }}</p>
                        <p class="text-[13px] text-slate">{{ $invitation->requirement->category->name }}</p>
                    </div>
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full bg-surface text-slate">
                        {{ ucfirst($invitation->status) }}
                    </span>
                </div>
            </button>

            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-line">
                @if ($invitation->message)
                    <p class="text-[13px] text-slate mb-3">&ldquo;{{ $invitation->message }}&rdquo;</p>
                @endif

                @if ($invitation->status === 'sent' || $invitation->status === 'viewed')
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="respond({{ $invitation->id }}, true)"
                                class="text-[13px] bg-green-600 hover:bg-green-700 text-white rounded-sm px-3 py-1.5 transition">
                            I'll submit an offer
                        </button>
                        <button type="button" wire:click="respond({{ $invitation->id }}, false)"
                                class="text-[13px] text-slate hover:text-ink">
                            Decline
                        </button>
                    </div>
                @else
                    <a href="{{ route('offers.submit', $invitation->requirement) }}" class="inline-block text-[13px] text-green-600 hover:underline">
                        Submit an offer &rarr;
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white border border-line rounded-lg p-6 text-center">
            <p class="text-[14px] text-slate">No invitations yet.</p>
        </div>
    @endforelse
</div>
