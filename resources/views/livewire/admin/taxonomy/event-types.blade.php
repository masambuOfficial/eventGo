<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.taxonomy.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Taxonomy</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Event types</h1>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">{{ $editingId ? 'Edit event type' : 'Add an event type' }}</h2>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Name</label>
                <input type="text" wire:model.blur="name"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('name') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Slug</label>
                <input type="text" wire:model.blur="slug"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('slug') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Icon (optional)</label>
                <input type="text" wire:model.blur="icon"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Sort order</label>
                <input type="number" wire:model.blur="sortOrder"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    {{ $editingId ? 'Save changes' : 'Add event type' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="text-[13px] text-slate hover:text-ink underline">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    @foreach ($items as $item)
        <div wire:key="event-type-{{ $item->id }}" class="bg-surface-raised border border-line rounded-lg p-6 mb-4 flex items-center justify-between">
            <div>
                <p class="text-[14px] font-medium text-ink">{{ $item->name }}</p>
                <p class="text-[13px] text-slate">{{ $item->slug }} &middot; sort {{ $item->sort_order }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $item->is_active ? 'bg-green-100 text-green-900' : 'bg-line text-slate' }}">
                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                </span>
                <button type="button" wire:click="edit({{ $item->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                <button type="button" wire:click="toggleActive({{ $item->id }})" wire:confirm="{{ $item->is_active ? 'Deactivate this event type? It will stop appearing as an option for new events.' : 'Reactivate this event type?' }}"
                        class="text-[13px] text-slate hover:text-ink underline">
                    {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    @endforeach
</div>
