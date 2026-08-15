<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\ShortlistEntry;
use App\Domain\Sourcing\States\OfferState\Rejected;
use App\Domain\Sourcing\States\OfferState\UnderReview;

class RejectOffer
{
    public function __invoke(Offer $offer): void
    {
        if ($offer->status->canTransitionTo(UnderReview::class)) {
            $offer->status->transitionTo(UnderReview::class);
        }

        $offer->status->transitionTo(Rejected::class);

        ShortlistEntry::where('requirement_id', $offer->requirement_id)->where('offer_id', $offer->id)->delete();
    }
}
