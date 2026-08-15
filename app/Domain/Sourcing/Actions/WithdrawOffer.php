<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\States\OfferState\Withdrawn;

class WithdrawOffer
{
    public function __invoke(Offer $offer): void
    {
        $offer->status->transitionTo(Withdrawn::class);
        $offer->forceFill(['withdrawn_at' => now()])->save();
    }
}
