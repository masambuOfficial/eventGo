<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Shortlisted as RequirementShortlisted;
use App\Domain\Sourcing\Actions\RejectOffer;
use App\Domain\Sourcing\Actions\ShortlistOffer;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\ShortlistEntry;
use App\Domain\Sourcing\States\OfferState\Rejected;
use App\Domain\Sourcing\States\OfferState\Shortlisted as OfferShortlisted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortlistAndRejectOfferTest extends TestCase
{
    use RefreshDatabase;

    private function submittedOffer(): Offer
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'offers_received']);

        return Offer::factory()->create(['requirement_id' => $requirement->id, 'status' => 'submitted']);
    }

    public function test_shortlisting_walks_a_submitted_offer_through_review_and_creates_an_entry(): void
    {
        $offer = $this->submittedOffer();

        (new ShortlistOffer)($offer, 'Best value for money.');

        $this->assertInstanceOf(OfferShortlisted::class, $offer->fresh()->status);
        $this->assertInstanceOf(RequirementShortlisted::class, $offer->fresh()->requirement->status);
        $this->assertDatabaseHas('shortlist_entries', [
            'requirement_id' => $offer->requirement_id,
            'offer_id' => $offer->id,
            'note' => 'Best value for money.',
        ]);
    }

    public function test_rejecting_a_submitted_offer_walks_it_through_review(): void
    {
        $offer = $this->submittedOffer();

        (new RejectOffer)($offer);

        $this->assertInstanceOf(Rejected::class, $offer->fresh()->status);
        $this->assertSame(0, ShortlistEntry::where('offer_id', $offer->id)->count());
    }
}
