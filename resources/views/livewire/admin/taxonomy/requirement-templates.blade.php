<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.taxonomy.index') }}" class="text-[13px] text-slate hover:text-ink">&larr; Taxonomy</a>

    <h1 class="text-[24px] font-semibold text-ink mt-2 mb-2">Requirement templates — {{ $eventType->name }}</h1>
    <p class="text-[13px] text-slate mb-6">
        These generate the editable requirements list an organiser sees for this event type. Expressions are
        checked against this event type's scope questions before saving — benchmark costs are launch estimates,
        not researched Kampala rates.
    </p>

    <div class="bg-surface-raised border border-line rounded-lg p-6 mb-6">
        <h2 class="text-[16px] font-semibold text-ink mb-4">{{ $editingId ? 'Edit template' : 'Add a template' }}</h2>

        <form wire:submit="save" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Service category</label>
                <select wire:model="serviceCategoryId" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="">Select a category…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('serviceCategoryId') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Priority</label>
                <select wire:model="priority" class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink">
                    <option value="essential">Essential</option>
                    <option value="important">Important</option>
                    <option value="optional">Optional</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[14px] font-medium text-ink mb-1">Quantity expression</label>
                <input type="text" wire:model.blur="quantityExpression" placeholder="e.g. ceil(guest_count * 1.05)"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] font-mono text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                <p class="mt-1 text-[12px] text-slate">Functions available: ceil, floor, round, min, max. Variables are this event type's scope question keys.</p>
                @error('quantityExpression') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="block text-[14px] font-medium text-ink mb-1">Condition expression (optional)</label>
                <input type="text" wire:model.blur="conditionExpr" placeholder="e.g. outdoor == true"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] font-mono text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                <p class="mt-1 text-[12px] text-slate">Leave blank to always include this line.</p>
                @error('conditionExpr') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Benchmark unit cost (UGX, optional)</label>
                <input type="number" wire:model.blur="benchmarkUnitCostUgx"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
                @error('benchmarkUnitCostUgx') <p class="mt-1 text-[13px] text-amber-700">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Sort order</label>
                <input type="number" wire:model.blur="sortOrder"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            <div>
                <label class="block text-[14px] font-medium text-ink mb-1">Default title (optional)</label>
                <input type="text" wire:model.blur="defaultTitle"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-[14px] font-medium text-ink mb-1">Default notes (optional)</label>
                <input type="text" wire:model.blur="defaultNotes"
                       class="w-full border border-line rounded-sm px-3 py-2 text-[16px] text-ink focus:outline-none focus:ring-2 focus:ring-green-600">
            </div>

            <div class="sm:col-span-2 flex gap-3">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                        class="bg-green-600 hover:bg-green-700 text-white rounded-sm px-4 py-2 text-[14px] font-medium transition disabled:opacity-60">
                    {{ $editingId ? 'Save changes' : 'Add template' }}
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
            <div wire:key="template-{{ $item->id }}" class="p-4 flex items-center justify-between">
                <div>
                    <p class="text-[14px] font-medium text-ink">{{ $item->category->name }}</p>
                    <p class="text-[13px] text-slate font-mono">{{ $item->quantity_expression }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[12px] font-medium px-2 py-0.5 rounded-full {{ $item->is_active ? 'bg-green-100 text-green-900' : 'bg-line text-slate' }}">
                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <button type="button" wire:click="edit({{ $item->id }})" class="text-[13px] text-green-600 hover:underline">Edit</button>
                    <button type="button" wire:click="toggleActive({{ $item->id }})" wire:confirm="{{ $item->is_active ? 'Deactivate this template?' : 'Reactivate this template?' }}"
                            class="text-[13px] text-slate hover:text-ink underline">
                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
