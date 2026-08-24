<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Two levels only, enforced here rather than trusted to the form: a
 * category cannot be assigned a parent that itself already has a parent
 * (matches the seeded shape — 7 top-level categories, each with several
 * children).
 */
#[Title('Service categories')]
class ServiceCategories extends Component
{
    public ?int $editingId = null;

    public ?int $parentId = null;

    public string $name = '';

    public string $slug = '';

    public string $icon = '';

    public string $unitLabel = '';

    public bool $requiresCapacity = false;

    public int $sortOrder = 0;

    public function topLevel(): Collection
    {
        return ServiceCategory::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function parentOptions(): Collection
    {
        return ServiceCategory::whereNull('parent_id')->orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $category = ServiceCategory::findOrFail($id);

        $this->editingId = $category->id;
        $this->parentId = $category->parent_id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->icon = (string) $category->icon;
        $this->unitLabel = (string) $category->unit_label;
        $this->requiresCapacity = $category->requires_capacity;
        $this->sortOrder = $category->sort_order;
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'parentId', 'name', 'slug', 'icon', 'unitLabel', 'requiresCapacity', 'sortOrder']);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:service_categories,slug,'.($this->editingId ?: 'NULL').',id'],
            'icon' => ['nullable', 'string', 'max:40'],
            'unitLabel' => ['nullable', 'string', 'max:40'],
            'sortOrder' => ['required', 'integer'],
        ]);

        if ($this->parentId) {
            $parent = ServiceCategory::find($this->parentId);

            if (! $parent || $parent->parent_id !== null) {
                $this->addError('parentId', 'A category can only be nested one level deep.');

                return;
            }

            if ($this->editingId === $this->parentId) {
                $this->addError('parentId', 'A category cannot be its own parent.');

                return;
            }
        }

        if ($this->parentId && $this->editingId && ServiceCategory::where('parent_id', $this->editingId)->exists()) {
            $this->addError('parentId', 'This category already has children of its own — it cannot also become a child category.');

            return;
        }

        ServiceCategory::updateOrCreate(
            ['id' => $this->editingId],
            [
                'parent_id' => $this->parentId,
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon ?: null,
                'unit_label' => $this->unitLabel ?: null,
                'requires_capacity' => $this->requiresCapacity,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->cancel();
    }

    public function toggleActive(int $id): void
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
    }

    public function updatedName(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.service-categories', [
            'topLevel' => $this->topLevel(),
            'parentOptions' => $this->parentOptions(),
        ]);
    }
}
