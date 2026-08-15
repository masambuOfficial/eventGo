<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Sourcing;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\PublishOpportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishOpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_creates_an_opportunity_and_moves_the_requirement_into_sourcing(): void
    {
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'district_id' => $district->id]);
        $requirement = Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => $category->id,
            'status' => 'open',
        ]);

        $provider = Provider::factory()->create();
        $provider->services()->create(['service_category_id' => $category->id]);
        $provider->serviceAreas()->attach($district->id);

        $opportunity = (new PublishOpportunity(
            app(\App\Domain\Sourcing\Actions\MatchProvidersToRequirement::class),
            app(\App\Domain\Attribution\Actions\RecordProviderLead::class),
        ))($requirement, ['closes_at' => now()->addWeek()]);

        $this->assertNotNull($opportunity->id);
        $this->assertInstanceOf(Sourcing::class, $requirement->fresh()->status);

        $this->assertDatabaseHas('provider_leads', [
            'provider_id' => $provider->id,
            'requirement_id' => $requirement->id,
            'source' => 'opportunity_match',
        ]);

        $lead = ProviderLead::where('provider_id', $provider->id)->first();
        $this->assertNotNull($lead->notified_at);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->owner_user_id,
            'type' => 'opportunity_matched',
        ]);

        $notification = Notification::where('user_id', $provider->owner_user_id)->first();
        $this->assertSame($requirement->id, $notification->payload['requirement_id']);
    }
}
