<?php

namespace Tests\Feature\Providers;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Opportunity;
use App\Livewire\Providers\Opportunities\Browse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_provider_only_sees_opportunities_matching_their_category_and_district(): void
    {
        $district = District::factory()->create();
        $otherDistrict = District::factory()->create();
        $category = ServiceCategory::factory()->create();

        $provider = Provider::factory()->create();
        $provider->services()->create(['service_category_id' => $category->id]);
        $provider->serviceAreas()->attach($district->id);

        $matchingEvent = Event::factory()->create(['event_type_id' => EventType::factory(), 'district_id' => $district->id]);
        $matchingRequirement = Requirement::factory()->create(['event_id' => $matchingEvent->id, 'service_category_id' => $category->id]);
        Opportunity::create(['requirement_id' => $matchingRequirement->id, 'status' => 'open', 'published_at' => now()]);

        $otherEvent = Event::factory()->create(['event_type_id' => EventType::factory(), 'district_id' => $otherDistrict->id]);
        $otherRequirement = Requirement::factory()->create(['event_id' => $otherEvent->id, 'service_category_id' => $category->id]);
        Opportunity::create(['requirement_id' => $otherRequirement->id, 'status' => 'open', 'published_at' => now()]);

        Livewire::actingAs($provider->owner)
            ->test(Browse::class)
            ->assertSee($matchingRequirement->title)
            ->assertDontSee($otherRequirement->title);
    }

    public function test_viewing_an_opportunity_records_a_lead(): void
    {
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();

        $provider = Provider::factory()->create();
        $provider->services()->create(['service_category_id' => $category->id]);
        $provider->serviceAreas()->attach($district->id);

        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'district_id' => $district->id]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => $category->id]);
        $opportunity = Opportunity::create(['requirement_id' => $requirement->id, 'status' => 'open', 'published_at' => now(), 'view_count' => 0]);

        Livewire::actingAs($provider->owner)
            ->test(Browse::class)
            ->call('view', $opportunity->id);

        $this->assertSame(1, $opportunity->fresh()->view_count);
        $this->assertDatabaseHas('provider_leads', [
            'provider_id' => $provider->id,
            'requirement_id' => $requirement->id,
        ]);
    }
}
