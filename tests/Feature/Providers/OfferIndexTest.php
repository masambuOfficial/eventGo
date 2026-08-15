<?php

namespace Tests\Feature\Providers;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;
use App\Livewire\Providers\Offers\Index;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfferIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_provider_can_withdraw_their_own_submitted_offer(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $provider = Provider::factory()->create();
        $offer = Offer::factory()->create(['requirement_id' => $requirement->id, 'provider_id' => $provider->id, 'status' => 'submitted']);

        Livewire::actingAs($provider->owner)
            ->test(Index::class)
            ->call('withdraw', $offer->id);

        $this->assertSame('withdrawn', $offer->fresh()->status->getValue());
    }
}
