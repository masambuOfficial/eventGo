<?php

namespace App\Livewire\Providers\Offers;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\AskClarification;
use App\Domain\Sourcing\Actions\SubmitOffer;
use App\Domain\Sourcing\Models\Clarification;
use App\Domain\Sourcing\Models\Offer;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Submit an offer')]
class Submit extends Component
{
    public int $requirementId;

    public int $providerId;

    public string $scopeSummary = '';

    public string $inclusions = '';

    public string $exclusions = '';

    public string $terms = '';

    public string $validUntil = '';

    public bool $availabilityConfirmed = false;

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    public string $newQuestion = '';

    public ?string $submittedStatus = null;

    public function mount(Requirement $requirement): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->requirementId = $requirement->id;
        $this->providerId = $provider->id;

        $existing = Offer::where('requirement_id', $requirement->id)->where('provider_id', $provider->id)->with('items')->first();

        if ($existing) {
            $this->scopeSummary = (string) $existing->scope_summary;
            $this->inclusions = (string) $existing->inclusions;
            $this->exclusions = (string) $existing->exclusions;
            $this->terms = (string) $existing->terms;
            $this->validUntil = $existing->valid_until?->format('Y-m-d') ?? '';
            $this->availabilityConfirmed = $existing->availability_confirmed;
            $this->items = $existing->items->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => (string) $item->quantity,
                'unit' => $item->unit,
                'unit_price_ugx' => $item->unit_price_ugx,
            ])->toArray();
            $this->submittedStatus = (string) $existing->status;
        }

        if (! $this->items) {
            $this->items = [$this->emptyItemRow()];
        }
    }

    protected function requirement(): Requirement
    {
        return Requirement::with(['event', 'category'])->findOrFail($this->requirementId);
    }

    public function addItemRow(): void
    {
        $this->items[] = $this->emptyItemRow();
    }

    public function removeItemRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function totalUgx(): float
    {
        return collect($this->items)->sum(fn (array $item) => (float) ($item['quantity'] ?: 0) * (float) ($item['unit_price_ugx'] ?: 0));
    }

    public function clarifications(): Collection
    {
        return Clarification::where('requirement_id', $this->requirementId)
            ->where('is_public', true)
            ->latest('created_at')
            ->get();
    }

    public function askQuestion(AskClarification $askClarification): void
    {
        $this->validate(['newQuestion' => ['required', 'string', 'max:1000']]);

        $askClarification($this->requirement(), auth()->user(), $this->newQuestion);

        $this->newQuestion = '';
    }

    public function submit(SubmitOffer $submitOffer): void
    {
        $this->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price_ugx' => ['required', 'integer', 'min:0'],
            'validUntil' => ['nullable', 'date', 'after:today'],
            'terms' => ['nullable', 'string', 'max:5000'],
        ]);

        $provider = Provider::findOrFail($this->providerId);

        try {
            $offer = $submitOffer($this->requirement(), $provider, auth()->user(), [
                'total_ugx' => (int) round($this->totalUgx()),
                'scope_summary' => $this->scopeSummary ?: null,
                'inclusions' => $this->inclusions ?: null,
                'exclusions' => $this->exclusions ?: null,
                'terms' => $this->terms ?: null,
                'valid_until' => $this->validUntil ?: null,
                'availability_confirmed' => $this->availabilityConfirmed,
                'items' => $this->items,
            ]);
        } catch (\RuntimeException $e) {
            $this->addError('items', $e->getMessage());

            return;
        }

        $this->submittedStatus = (string) $offer->fresh()->status;
    }

    private function emptyItemRow(): array
    {
        return [
            'description' => '',
            'quantity' => '1',
            'unit' => '',
            'unit_price_ugx' => '',
        ];
    }

    public function render()
    {
        return view('livewire.providers.offers.submit', [
            'requirement' => $this->requirement(),
            'clarifications' => $this->clarifications(),
            'alreadySubmitted' => $this->submittedStatus !== null && $this->submittedStatus !== 'draft',
        ]);
    }
}
