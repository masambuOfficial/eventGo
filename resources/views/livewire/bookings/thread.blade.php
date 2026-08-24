<div class="bg-surface-raised border border-line rounded-lg p-6" wire:poll.15s>
    <h2 class="text-[16px] font-semibold text-ink mb-4">Messages</h2>

    <div class="space-y-3 max-h-96 overflow-y-auto mb-4">
        @forelse ($messages as $message)
            <div wire:key="message-{{ $message->id }}" class="{{ $message->sender_user_id === auth()->id() ? 'text-right' : '' }}">
                <div class="inline-block max-w-[80%] {{ $message->sender_user_id === auth()->id() ? 'bg-green-100 text-green-900' : 'bg-surface text-ink' }} rounded-lg px-3 py-2 text-[14px]">
                    {{ $message->body }}
                    @if ($message->contains_contact_info)
                        <p class="text-[11px] text-amber-700 mt-1">May contain a phone number or email</p>
                    @endif
                </div>
                <p class="text-[12px] text-slate mt-0.5">{{ $message->sender->full_name }} · {{ $message->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <p class="text-[13px] text-slate">No messages yet.</p>
        @endforelse
    </div>

    <form wire:submit="sendMessage" class="flex gap-2">
        <input type="text" wire:model="body" placeholder="Write a message…"
               class="flex-1 border border-line rounded-sm px-3 py-2 text-[14px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
        <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
            Send
        </button>
    </form>
    @error('body') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
</div>
