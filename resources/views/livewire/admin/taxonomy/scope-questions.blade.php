<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.taxonomy.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Taxonomy</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-6">Scope questions — {{ $eventType->name }}</h1>

    @if ($deleteError)
        <div class="bg-amber-100 text-amber-700 text-[14px] rounded-lg px-4 py-3 mb-4">{{ $deleteError }}</div>
    @endif

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">{{ $editingId ? 'Edit question' : 'Add a question' }}</h2>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Key</label>
                <input type="text" wire:model.blur="key" placeholder="e.g. guest_count"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('key') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Label</label>
                <input type="text" wire:model.blur="label"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('label') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[14px] font-medium text-ink mb-1">Help text (optional)</label>
                <input type="text" wire:model.blur="helpText"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Input type</label>
                <select wire:model.live="inputType" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="bool">Yes / no</option>
                    <option value="select">Select (one option)</option>
                    <option value="multiselect">Select (multiple options)</option>
                    <option value="date">Date</option>
                </select>
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Sort order</label>
                <input type="number" wire:model.blur="sortOrder"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            @if (in_array($inputType, ['select', 'multiselect']))
                <div class="sm:col-span-2">
                    <label class="block text-[14px] font-medium text-ink mb-1">Options — one per line</label>
                    <textarea wire:model.blur="optionsText" rows="4"
                              class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600"></textarea>
                    @error('optionsText') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
                </div>
            @endif

            <label class="flex items-center gap-2 text-[14px] text-ink">
                <input type="checkbox" wire:model="isRequired" class="rounded-sm border-line">
                Required
            </label>

            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    {{ $editingId ? 'Save changes' : 'Add question' }}
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
            <div wire:key="question-{{ $item->id }}" class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">{{ $item->label }} <span class="text-slate font-normal">({{ $item->key }})</span></p>
                    <p class="text-[13px] text-slate">{{ ucfirst($item->input_type) }}{{ $item->is_required ? ' · required' : '' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="edit({{ $item->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                    <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Remove this question?"
                            class="text-[13px] text-amber-700 hover:underline">
                        Remove
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
