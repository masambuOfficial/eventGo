<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Clarification;
use App\Domain\Sourcing\Models\Offer;
use App\Livewire\Sourcing\OfferComparison;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfferComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_sees_offers_cheapest_first_and_can_shortlist(): void
    {
        $owner = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'offers_received']);

        $expensive = Offer::factory()->create(['requirement_id' => $requirement->id, 'status' => 'submitted', 'total_ugx' => 9_000_000]);
        $cheap = Offer::factory()->create(['requirement_id' => $requirement->id, 'status' => 'submitted', 'total_ugx' => 3_000_000]);

        $component = Livewire::actingAs($owner)->test(OfferComparison::class, ['requirement' => $requirement]);

        $offers = $component->viewData('offers');
        $this->assertTrue($offers->first()->is($cheap));

        $component->call('shortlist', $cheap->id)->assertHasNoErrors();

        $this->assertSame('shortlisted', $cheap->fresh()->status->getValue());
        $this->assertDatabaseHas('shortlist_entries', ['requirement_id' => $requirement->id, 'offer_id' => $cheap->id]);
    }

    public function test_owner_can_answer_a_clarification(): void
    {
        $owner = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $clarification = Clarification::create([
            'requirement_id' => $requirement->id,
            'asked_by_user_id' => User::factory()->create()->id,
            'question' => 'Is there parking?',
        ]);

        Livewire::actingAs($owner)
            ->test(OfferComparison::class, ['requirement' => $requirement])
            ->set("answers.{$clarification->id}", 'Yes, on site.')
            ->call('answer', $clarification->id);

        $this->assertSame('Yes, on site.', $clarification->fresh()->answer);
    }

    public function test_owner_can_accept_a_shortlisted_offer(): void
    {
        $owner = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id, 'starts_at' => now()->addMonth()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'shortlisted']);
        $offer = Offer::factory()->create(['requirement_id' => $requirement->id, 'status' => 'shortlisted', 'total_ugx' => 4_000_000]);

        Livewire::actingAs($owner)
            ->test(OfferComparison::class, ['requirement' => $requirement])
            ->call('accept', $offer->id)
            ->assertRedirect(route('events.dashboard', $event));

        $this->assertDatabaseHas('bookings', [
            'requirement_id' => $requirement->id,
            'offer_id' => $offer->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_a_non_owner_cannot_view_the_comparison(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);

        Livewire::actingAs($stranger)
            ->test(OfferComparison::class, ['requirement' => $requirement])
            ->assertForbidden();
    }
}
