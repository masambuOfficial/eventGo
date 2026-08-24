<?php

namespace Tests\Feature\Bookings;

use App\Domain\Attribution\Models\Connection;
use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Billing\Entitlements;
use App\Domain\Bookings\Actions\AcceptOffer;
use App\Domain\Bookings\States\BookingState\Confirmed;
use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Booked;
use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderAvailability;
use App\Domain\Sourcing\Actions\PublishOpportunity;
use App\Domain\Sourcing\Actions\ShortlistOffer;
use App\Domain\Sourcing\Actions\SubmitOffer;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\States\OfferState\Accepted;
use App\Domain\Sourcing\States\OfferState\Rejected;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_opportunity_to_booking_happy_path(): void
    {
        $organiser = User::factory()->create();
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();
        $event = Event::factory()->create([
            'event_type_id' => EventType::factory(),
            'owner_user_id' => $organiser->id,
            'district_id' => $district->id,
            'starts_at' => now()->addMonth()->startOfDay(),
        ]);
        $requirement = Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => $category->id,
            'status' => 'open',
        ]);

        $winner = Provider::factory()->create();
        $winner->services()->create(['service_category_id' => $category->id]);
        $winner->serviceAreas()->attach($district->id);

        $loser = Provider::factory()->create();
        $loser->services()->create(['service_category_id' => $category->id]);
        $loser->serviceAreas()->attach($district->id);

        (new PublishOpportunity(app(\App\Domain\Sourcing\Actions\MatchProvidersToRequirement::class), app(\App\Domain\Attribution\Actions\RecordProviderLead::class)))($requirement);

        $submitOffer = new SubmitOffer(new Entitlements);
        $winningOffer = $submitOffer($requirement->fresh(), $winner, $winner->owner, [
            'total_ugx' => 4_000_000,
            'items' => [['description' => 'Service', 'quantity' => 1, 'unit_price_ugx' => 4_000_000]],
        ]);
        $losingOffer = $submitOffer($requirement->fresh(), $loser, $loser->owner, [
            'total_ugx' => 5_000_000,
            'items' => [['description' => 'Service', 'quantity' => 1, 'unit_price_ugx' => 5_000_000]],
        ]);

        (new ShortlistOffer)($winningOffer->fresh());

        $booking = (new AcceptOffer)($winningOffer->fresh());

        $this->assertInstanceOf(Confirmed::class, $booking->status);
        $this->assertSame($winner->id, $booking->provider_id);
        $this->assertSame(4_000_000, $booking->agreed_total_ugx);
        $this->assertNotNull($booking->contacts_released_at);

        $this->assertInstanceOf(Booked::class, $requirement->fresh()->status);
        $this->assertSame($winningOffer->id, $requirement->fresh()->selected_offer_id);
        $this->assertSame($booking->id, $requirement->fresh()->booking_id);

        $this->assertInstanceOf(Accepted::class, $winningOffer->fresh()->status);
        $this->assertInstanceOf(Rejected::class, $losingOffer->fresh()->status);

        $winnerLead = ProviderLead::where('provider_id', $winner->id)->where('requirement_id', $requirement->id)->first();
        $this->assertSame('won', $winnerLead->outcome);
        $this->assertSame(4_000_000, $winnerLead->value_ugx);

        $loserLead = ProviderLead::where('provider_id', $loser->id)->where('requirement_id', $requirement->id)->first();
        $this->assertSame('lost', $loserLead->outcome);

        $connection = Connection::where('organiser_user_id', $organiser->id)->where('provider_id', $winner->id)->first();
        $this->assertNotNull($connection);
        $this->assertSame('working', $connection->status);
        $this->assertSame(1, $connection->bookings_count);
        $this->assertSame(4_000_000, $connection->bookings_value_ugx);

        $availability = ProviderAvailability::where('provider_id', $winner->id)
            ->where('date', $event->fresh()->starts_at->toDateString())
            ->first();
        $this->assertNotNull($availability);
        $this->assertSame(1, $availability->capacity_used);

        $this->assertGreaterThan(0, $booking->tasks()->count());

        $thread = \App\Domain\Messaging\Models\Thread::where('subject_type', 'booking')->where('subject_id', $booking->id)->first();
        $this->assertNotNull($thread);
        $this->assertTrue($thread->participants->pluck('id')->contains($organiser->id));
        $this->assertTrue($thread->participants->pluck('id')->contains($winner->owner->id));
    }

    public function test_accepting_requires_the_offer_to_be_shortlisted_first(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'offers_received']);
        $offer = Offer::factory()->create(['requirement_id' => $requirement->id, 'status' => 'submitted']);

        $this->expectException(\Spatie\ModelStates\Exceptions\CouldNotPerformTransition::class);

        (new AcceptOffer)($offer);
    }
}
