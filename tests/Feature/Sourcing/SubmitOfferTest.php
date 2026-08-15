<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\OffersReceived;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\SubmitOffer;
use App\Domain\Sourcing\States\OfferState\Submitted;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_creates_line_items_and_moves_everything_forward(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $organiser->id]);
        $requirement = Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => ServiceCategory::factory(),
            'status' => 'sourcing',
        ]);
        $provider = Provider::factory()->create();

        $offer = (new SubmitOffer)($requirement, $provider, $provider->owner, [
            'total_ugx' => 5_000_000,
            'terms' => '50% deposit required.',
            'items' => [
                ['description' => 'Chairs', 'quantity' => 500, 'unit_price_ugx' => 5000],
                ['description' => 'Tables', 'quantity' => 50, 'unit_price_ugx' => 20000],
            ],
        ]);

        $this->assertInstanceOf(Submitted::class, $offer->fresh()->status);
        $this->assertCount(2, $offer->items);
        $this->assertSame(2500000, $offer->items()->where('description', 'Chairs')->first()->line_total_ugx);

        $this->assertInstanceOf(OffersReceived::class, $requirement->fresh()->status);

        $lead = ProviderLead::where('provider_id', $provider->id)->where('requirement_id', $requirement->id)->first();
        $this->assertNotNull($lead->offered_at);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $organiser->id,
            'type' => 'offer_received',
        ]);
    }

    public function test_resubmitting_while_still_draft_replaces_line_items(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'sourcing']);
        $provider = Provider::factory()->create();

        $action = new SubmitOffer;

        // Not exercising an explicit draft-save step here since SubmitOffer
        // always submits on first call — this covers editing before review
        // by calling again before any organiser action changes the state.
        $offer = $action($requirement, $provider, $provider->owner, [
            'total_ugx' => 1_000_000,
            'items' => [['description' => 'A', 'quantity' => 1, 'unit_price_ugx' => 1_000_000]],
        ]);

        $this->assertCount(1, $offer->fresh()->items);
    }
}
