<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\States\OfferState\Accepted;
use App\Domain\Sourcing\States\OfferState\Draft;
use App\Domain\Sourcing\States\OfferState\Rejected;
use App\Domain\Sourcing\States\OfferState\Shortlisted;
use App\Domain\Sourcing\States\OfferState\Submitted;
use App\Domain\Sourcing\States\OfferState\UnderReview;
use App\Domain\Sourcing\States\OfferState\Withdrawn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Tests\TestCase;

class OfferStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_defaults_to_draft(): void
    {
        $offer = Offer::factory()->create(['status' => null]);

        $this->assertInstanceOf(Draft::class, $offer->status);
    }

    public function test_offer_follows_the_happy_path_to_accepted(): void
    {
        $offer = Offer::factory()->create(['status' => Draft::class]);

        $offer->status->transitionTo(Submitted::class);
        $offer->status->transitionTo(UnderReview::class);
        $offer->status->transitionTo(Shortlisted::class);
        $offer->status->transitionTo(Accepted::class);

        $this->assertInstanceOf(Accepted::class, $offer->fresh()->status);
    }

    public function test_offer_can_be_withdrawn_only_while_submitted(): void
    {
        $offer = Offer::factory()->create(['status' => Draft::class]);
        $offer->status->transitionTo(Submitted::class);

        $offer->status->transitionTo(Withdrawn::class);

        $this->assertInstanceOf(Withdrawn::class, $offer->fresh()->status);
    }

    public function test_offer_cannot_be_accepted_directly_from_draft(): void
    {
        $offer = Offer::factory()->create(['status' => Draft::class]);

        $this->expectException(CouldNotPerformTransition::class);

        $offer->status->transitionTo(Accepted::class);
    }

    public function test_offer_cannot_be_withdrawn_once_under_review(): void
    {
        $offer = Offer::factory()->create(['status' => Draft::class]);
        $offer->status->transitionTo(Submitted::class);
        $offer->status->transitionTo(UnderReview::class);

        $this->expectException(CouldNotPerformTransition::class);

        $offer->status->transitionTo(Withdrawn::class);
    }

    public function test_offer_cannot_transition_out_of_rejected(): void
    {
        $offer = Offer::factory()->create(['status' => Rejected::class]);

        $this->expectException(CouldNotPerformTransition::class);

        $offer->status->transitionTo(Shortlisted::class);
    }
}
