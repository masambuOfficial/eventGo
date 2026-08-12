<div class="max-w-2xl mx-auto">
    <a href="{{ route('provider.dashboard') }}" class="text-[13px] text-slate hover:text-ink">&larr; Back to dashboard</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-1">Portfolio</h1>
    <p class="text-[14px] text-slate mb-6">Photos of your past work. Organisers see these on your profile.</p>

    <form wire:submit="save" class="bg-white border border-line rounded-lg p-6 mb-6 space-y-4">
        <div>
            <label class="block text-[14px] font-medium text-ink mb-1">Photo</label>
            <input type="file" wire:model="upload" accept="image/*"
                   class="w-full text-[14px] text-ink">
            <div wire:loading wire:target="upload" class="text-[13px] text-slate mt-1">Uploading…</div>
            @error('upload') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror

            @if ($upload)
                <img src="{{ $upload->temporaryUrl() }}" class="mt-3 h-32 rounded-sm object-cover">
            @endif
        </div>

        <div>
            <label class="block text-[14px] font-medium text-ink mb-1">Caption (optional)</label>
            <input type="text" wire:model="caption"
                   class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
            <span wire:loading.remove wire:target="save">Add photo</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </form>

    <div class="grid grid-cols-3 gap-3">
        @forelse ($items as $item)
            <div wire:key="media-{{ $item->id }}" class="relative">
                <img src="{{ Storage::disk('public')->url($item->path) }}" class="w-full h-28 object-cover rounded-sm border border-line">
                <button wire:click="remove({{ $item->id }})" wire:confirm="Remove this photo?"
                        class="absolute top-1 right-1 bg-white/90 text-[12px] text-amber-700 rounded-sm px-2 py-0.5">
                    Remove
                </button>
            </div>
        @empty
            <p class="text-[14px] text-slate col-span-3">No photos yet.</p>
        @endforelse
    </div>
</div>
