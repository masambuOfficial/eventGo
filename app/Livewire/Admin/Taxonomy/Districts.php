<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Catalog\Models\District;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Unlike the other taxonomy screens this one sees real ongoing use —
 * DistrictSeeder's own comment: "Uganda creates and splits districts
 * regularly... do not treat this as authoritative."
 */
#[Title('Districts')]
class Districts extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $region = 'central';

    public string $effectiveFrom = '';

    public function items(): Collection
    {
        return District::orderBy('region')->orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $district = District::findOrFail($id);

        $this->editingId = $district->id;
        $this->name = $district->name;
        $this->region = $district->region;
        $this->effectiveFrom = $district->effective_from?->format('Y-m-d') ?? '';
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'effectiveFrom']);
        $this->region = 'central';
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:80', 'unique:districts,name,'.($this->editingId ?: 'NULL').',id'],
            'region' => ['required', 'in:central,eastern,northern,western'],
            'effectiveFrom' => ['nullable', 'date'],
        ]);

        District::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'region' => $this->region,
                'effective_from' => $this->effectiveFrom ?: null,
            ]
        );

        $this->cancel();
    }

    public function toggleActive(int $id): void
    {
        $district = District::findOrFail($id);
        $district->update(['is_active' => ! $district->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.districts', [
            'items' => $this->items(),
        ]);
    }
}
