<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.taxonomy.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Taxonomy</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Service categories</h1>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">{{ $editingId ? 'Edit category' : 'Add a category' }}</h2>

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
                <label class="block text-[14px] font-medium text-ink mb-1">Parent category (optional)</label>
                <select wire:model="parentId" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="">None — top level</option>
                    @foreach ($parentOptions as $option)
                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                    @endforeach
                </select>
                @error('parentId') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Unit label (optional)</label>
                <input type="text" wire:model.blur="unitLabel" placeholder="e.g. per plate"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
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

            <label class="flex items-center gap-2 text-[14px] text-ink">
                <input type="checkbox" wire:model="requiresCapacity" class="rounded-sm border-line">
                Requires a guest-capacity match (venues, catering, seating…)
            </label>

            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    {{ $editingId ? 'Save changes' : 'Add category' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancel" class="text-[13px] text-slate hover:text-ink underline">
                        Cancel
                    </button>
                @endif
            </div>
        </form>
    </div>

    @foreach ($topLevel as $parent)
        <div wire:key="category-{{ $parent->id }}" class="bg-surface-raised border border-line rounded-lg p-6 mb-4">
            <div class="flex items-center justify-between">
                <p class="text-[15px] font-semibold text-ink">{{ $parent->name }}</p>
                <div class="flex items-center gap-3">
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $parent->is_active ? 'bg-green-100 text-green-900' : 'bg-line text-slate' }}">
                        {{ $parent->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <button type="button" wire:click="edit({{ $parent->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                    <button type="button" wire:click="toggleActive({{ $parent->id }})" wire:confirm="{{ $parent->is_active ? 'Deactivate this category?' : 'Reactivate this category?' }}"
                            class="text-[13px] text-slate hover:text-ink underline">
                        {{ $parent->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>

            @if ($parent->children->isNotEmpty())
                <div class="mt-3 pl-4 border-l-2 border-line space-y-2">
                    @foreach ($parent->children as $child)
                        <div wire:key="category-{{ $child->id }}" class="flex items-center justify-between">
                            <p class="text-[14px] text-ink">{{ $child->name }}</p>
                            <div class="flex items-center gap-3">
                                <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $child->is_active ? 'bg-green-100 text-green-900' : 'bg-line text-slate' }}">
                                    {{ $child->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                <button type="button" wire:click="edit({{ $child->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                                <button type="button" wire:click="toggleActive({{ $child->id }})" wire:confirm="{{ $child->is_active ? 'Deactivate this category?' : 'Reactivate this category?' }}"
                                        class="text-[13px] text-slate hover:text-ink underline">
                                    {{ $child->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
