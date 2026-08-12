<?php

namespace App\Livewire\Providers;

use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderAvailability;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Availability')]
class Availability extends Component
{
    public int $providerId;

    public string $date = '';

    public int $capacity_total = 1;

    public bool $is_blackout = false;

    public string $note = '';

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    protected function provider(): Provider
    {
        return Provider::findOrFail($this->providerId);
    }

    public function entries(): Collection
    {
        return $this->provider()->availability()
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->get();
    }

    public function addEntry(): void
    {
        $data = $this->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'capacity_total' => ['required', 'integer', 'min:0', 'max:50'],
            'is_blackout' => ['boolean'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        ProviderAvailability::updateOrCreate(
            ['provider_id' => $this->providerId, 'date' => $data['date']],
            [
                'capacity_total' => $data['is_blackout'] ? 0 : $data['capacity_total'],
                'is_blackout' => $data['is_blackout'],
                'note' => $data['note'] ?: null,
            ]
        );

        $this->reset(['date', 'capacity_total', 'is_blackout', 'note']);
        $this->capacity_total = 1;
    }

    public function removeEntry(int $id): void
    {
        $this->provider()->availability()->where('id', $id)->delete();
    }

    public function render()
    {
        return view('livewire.providers.availability', [
            'entries' => $this->entries(),
        ]);
    }
}
