<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.taxonomy.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Taxonomy</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-2">Districts</h1>
    <p class="text-[13px] text-slate mb-6">
        A starter list, not the official UBOS register — expect to add and adjust these over time.
    </p>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">{{ $editingId ? 'Edit district' : 'Add a district' }}</h2>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Name</label>
                <input type="text" wire:model.blur="name"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('name') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Region</label>
                <select wire:model="region" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="central">Central</option>
                    <option value="eastern">Eastern</option>
                    <option value="northern">Northern</option>
                    <option value="western">Western</option>
                </select>
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Effective from (optional)</label>
                <input type="date" wire:model.blur="effectiveFrom"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            <div class="sm:col-span-3 flex gap-3">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    {{ $editingId ? 'Save changes' : 'Add district' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="text-[13px] text-slate hover:text-ink underline">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-surface-raised border border-line rounded-lg divide-y divide-line">
        @foreach ($items as $item)
            <div wire:key="district-{{ $item->id }}" class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">{{ $item->name }}</p>
                    <p class="text-[13px] text-slate">{{ ucfirst($item->region) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $item->is_active ? 'bg-green-100 text-green-900' : 'bg-line text-slate' }}">
                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <button type="button" wire:click="edit({{ $item->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                    <button type="button" wire:click="toggleActive({{ $item->id }})" wire:confirm="{{ $item->is_active ? 'Deactivate this district?' : 'Reactivate this district?' }}"
                            class="text-[13px] text-slate hover:text-ink underline">
                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
