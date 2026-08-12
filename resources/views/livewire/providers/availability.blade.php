<div class="max-w-2xl mx-auto">
    <a href="{{ route('provider.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back to dashboard</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">Availability</h1>
    <p class="text-[14px] text-slate mb-6">
        Set how many bookings you can take on a date, or mark it as unavailable. Applies from today onward.
    </p>

    <form wire:submit="addEntry" class="bg-white border border-line rounded-lg p-6 mb-6 space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Date <span class="text-amber-700">*</span></label>
                <input type="date" wire:model="date"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('date') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Capacity that day <span class="text-amber-700">*</span></label>
                <input type="number" min="0" wire:model="capacity_total" @disabled($is_blackout)
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600 disabled:bg-surface">
            </div>
        </div>

        <label class="flex items-center gap-2 text-[14px] text-ink">
            <input type="checkbox" wire:model="is_blackout" class="rounded-sm border-line">
            Unavailable this date
        </label>

        <div>
            <label class="block text-[14px] font-medium text-ink mb-1">Note (optional)</label>
            <input type="text" wire:model="note"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
        </div>

        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition">
            Save date
        </button>
    </form>

    <div class="bg-white border border-line rounded-lg p-6">
        <h2 class="text-[18px] font-semibold text-ink mb-3">Upcoming</h2>

        @forelse ($entries as $entry)
            <div wire:key="entry-{{ $entry->id }}" class="flex items-center justify-between py-2 border-b border-line last:border-0">
                <div>
                    <span class="text-[14px] text-ink">{{ $entry->date->format('D, j M Y') }}</span>
                    @if ($entry->is_blackout)
                        <span class="ml-2 text-[13px] text-amber-700">Unavailable</span>
                    @else
                        <span class="ml-2 text-[13px] text-slate">Capacity {{ $entry->capacity_used }}/{{ $entry->capacity_total }}</span>
                    @endif
                    @if ($entry->note)
                        <span class="ml-2 text-[13px] text-slate">— {{ $entry->note }}</span>
                    @endif
                </div>
                <button wire:click="removeEntry({{ $entry->id }})" wire:confirm="Remove this date?"
                        class="text-[13px] text-slate hover:text-amber-700">Remove</button>
            </div>
        @empty
            <p class="text-[14px] text-slate">No dates set yet — you're assumed available everywhere until you add exceptions.</p>
        @endforelse
    </div>
</div>
