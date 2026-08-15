<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Sourcing;
use App\Domain\Providers\Models\Provider;
use App\Livewire\Sourcing\OpportunityPanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_publish_an_opportunity(): void
    {
        $owner = User::factory()->create();
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id, 'district_id' => $district->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => $category->id, 'status' => 'open']);

        Livewire::actingAs($owner)
            ->test(OpportunityPanel::class, ['requirement' => $requirement])
            ->set('budgetVisible', true)
            ->set('budgetMinUgx', 1_000_000)
            ->set('budgetMaxUgx', 2_000_000)
            ->call('publish')
            ->assertHasNoErrors();

        $this->assertInstanceOf(Sourcing::class, $requirement->fresh()->status);
        $this->assertDatabaseHas('opportunities', [
            'requirement_id' => $requirement->id,
            'status' => 'open',
            'budget_visible' => 1,
        ]);
    }

    public function test_owner_can_invite_a_matched_provider(): void
    {
        $owner = User::factory()->create();
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id, 'district_id' => $district->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => $category->id, 'status' => 'open']);

        $provider = Provider::factory()->create();
        $provider->services()->create(['service_category_id' => $category->id]);
        $provider->serviceAreas()->attach($district->id);

        Livewire::actingAs($owner)
            ->test(OpportunityPanel::class, ['requirement' => $requirement])
            ->call('invite', $provider->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invitations', [
            'requirement_id' => $requirement->id,
            'provider_id' => $provider->id,
            'status' => 'sent',
        ]);
    }

    public function test_a_non_owner_cannot_view_the_panel(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);

        Livewire::actingAs($stranger)
            ->test(OpportunityPanel::class, ['requirement' => $requirement])
            ->assertForbidden();
    }
}
