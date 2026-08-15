<?php

namespace Tests\Feature\Providers;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Livewire\Providers\Offers\Submit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfferSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_provider_can_submit_an_offer_with_line_items(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'sourcing']);
        $provider = Provider::factory()->create();

        Livewire::actingAs($provider->owner)
            ->test(Submit::class, ['requirement' => $requirement])
            ->set('items.0.description', 'Chairs')
            ->set('items.0.quantity', '500')
            ->set('items.0.unit_price_ugx', '5000')
            ->set('terms', '50% deposit.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('offers', [
            'requirement_id' => $requirement->id,
            'provider_id' => $provider->id,
            'total_ugx' => 2_500_000,
            'status' => 'submitted',
        ]);
    }

    public function test_a_question_can_be_asked_from_the_submit_page(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $provider = Provider::factory()->create();

        Livewire::actingAs($provider->owner)
            ->test(Submit::class, ['requirement' => $requirement])
            ->set('newQuestion', 'Is there power on site?')
            ->call('askQuestion')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clarifications', [
            'requirement_id' => $requirement->id,
            'question' => 'Is there power on site?',
        ]);
    }
}
