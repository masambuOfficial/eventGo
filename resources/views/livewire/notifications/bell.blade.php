<div class="relative" x-data="{ open: false }" wire:poll.30s>
    <button type="button" @click="open = !open" @click.outside="open = false" class="relative text-slate hover:text-ink">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-amber-500 text-white text-[10px] rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
         class="absolute right-0 mt-2 w-80 bg-surface-raised border border-line rounded-lg shadow-lg z-10">
        <div class="flex items-center justify-between px-4 py-3 border-b border-line">
            <span class="text-[13px] font-medium text-ink">Notifications</span>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllRead" class="text-[12px] text-green-600 hover:underline">Mark all read</button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($recent as $notification)
                <div wire:key="notification-{{ $notification->id }}" class="px-4 py-3 border-t border-line first:border-t-0 {{ $notification->read_at ? '' : 'bg-green-50' }}">
                    <p class="text-[13px] text-ink">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</p>
                    <p class="text-[12px] text-slate">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="px-4 py-3 text-[13px] text-slate">Nothing yet.</p>
            @endforelse
        </div>
    </div>
</div>
