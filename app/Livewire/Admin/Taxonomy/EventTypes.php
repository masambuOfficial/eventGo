<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Catalog\Models\EventType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Event types')]
class EventTypes extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $icon = '';

    public int $sortOrder = 0;

    public function items(): Collection
    {
        return EventType::orderBy('sort_order')->orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $eventType = EventType::findOrFail($id);

        $this->editingId = $eventType->id;
        $this->name = $eventType->name;
        $this->slug = $eventType->slug;
        $this->icon = (string) $eventType->icon;
        $this->sortOrder = $eventType->sort_order;
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'slug', 'icon', 'sortOrder']);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:event_types,slug,'.($this->editingId ?: 'NULL').',id'],
            'icon' => ['nullable', 'string', 'max:40'],
            'sortOrder' => ['required', 'integer'],
        ]);

        EventType::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon ?: null,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->cancel();
    }

    public function toggleActive(int $id): void
    {
        $eventType = EventType::findOrFail($id);
        $eventType->update(['is_active' => ! $eventType->is_active]);
    }

    public function updatedName(string $value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.event-types', [
            'items' => $this->items(),
        ]);
    }
}
