<?php

namespace App\Livewire\Providers\Opportunities;

use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\RecordOpportunityView;
use App\Domain\Sourcing\Models\Opportunity;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Opportunities')]
class Browse extends Component
{
    public int $providerId;

    /** @var array<int, bool> */
    public array $viewed = [];

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    public function opportunities(): Collection
    {
        $provider = Provider::with('services', 'serviceAreas')->find($this->providerId);
        $categoryIds = $provider->services->pluck('service_category_id');
        $districtIds = $provider->serviceAreas->pluck('id');

        return Opportunity::query()
            ->where('status', 'open')
            ->whereHas(
                'requirement',
                fn ($query) => $query->whereIn('service_category_id', $categoryIds)
                    ->whereHas('event', fn ($eventQuery) => $eventQuery->whereIn('district_id', $districtIds))
            )
            ->with(['requirement.event', 'requirement.category'])
            ->latest('published_at')
            ->get();
    }

    public function view(int $opportunityId, RecordOpportunityView $recordView): void
    {
        if (isset($this->viewed[$opportunityId])) {
            return;
        }

        $opportunity = Opportunity::findOrFail($opportunityId);
        $provider = Provider::findOrFail($this->providerId);

        $recordView($opportunity, $provider);

        $this->viewed[$opportunityId] = true;
    }

    public function render()
    {
        return view('livewire.providers.opportunities.browse', [
            'opportunities' => $this->opportunities(),
        ]);
    }
}
