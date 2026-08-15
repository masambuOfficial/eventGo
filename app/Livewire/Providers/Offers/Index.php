<?php

namespace App\Livewire\Providers\Offers;

use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\WithdrawOffer;
use App\Domain\Sourcing\Models\Offer;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Your offers')]
class Index extends Component
{
    public int $providerId;

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    public function offers(): Collection
    {
        return Offer::where('provider_id', $this->providerId)
            ->with(['requirement.event', 'requirement.category'])
            ->latest('submitted_at')
            ->get();
    }

    public function withdraw(int $offerId, WithdrawOffer $withdrawOffer): void
    {
        $offer = Offer::where('id', $offerId)->where('provider_id', $this->providerId)->firstOrFail();

        $withdrawOffer($offer);
    }

    public function render()
    {
        return view('livewire.providers.offers.index', [
            'offers' => $this->offers(),
        ]);
    }
}
