<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Events\States\RequirementState\Shortlisted as RequirementShortlisted;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\ShortlistEntry;
use App\Domain\Sourcing\States\OfferState\Shortlisted as OfferShortlisted;
use App\Domain\Sourcing\States\OfferState\UnderReview;

/**
 * Shortlisting is also how an offer enters review — architecture's offer
 * diagram has no separate "start review" action, so an organiser choosing
 * to shortlist an offer that's still `submitted` walks it through
 * `under_review` on the way, in the same call.
 */
class ShortlistOffer
{
    public function __invoke(Offer $offer, ?string $note = null): void
    {
        if ($offer->status->canTransitionTo(UnderReview::class)) {
            $offer->status->transitionTo(UnderReview::class);
        }

        if ($offer->status->canTransitionTo(OfferShortlisted::class)) {
            $offer->status->transitionTo(OfferShortlisted::class);
        }

        ShortlistEntry::updateOrCreate(
            ['requirement_id' => $offer->requirement_id, 'offer_id' => $offer->id],
            ['note' => $note, 'created_at' => now()]
        );

        $requirement = $offer->requirement;

        if ($requirement->status->canTransitionTo(RequirementShortlisted::class)) {
            $requirement->status->transitionTo(RequirementShortlisted::class);
        }
    }
}
