<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Attribution\Actions\RecordProviderLead;
use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\RecordOpportunityView;
use App\Domain\Sourcing\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordOpportunityViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_increments_view_count_and_records_a_lead(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $opportunity = Opportunity::create(['requirement_id' => $requirement->id, 'status' => 'open', 'view_count' => 0]);
        $provider = Provider::factory()->create();

        (new RecordOpportunityView(app(RecordProviderLead::class)))($opportunity, $provider);

        $this->assertSame(1, $opportunity->fresh()->view_count);

        $lead = ProviderLead::where('provider_id', $provider->id)->where('requirement_id', $requirement->id)->first();
        $this->assertSame('search', $lead->source);
        $this->assertNotNull($lead->viewed_at);
    }

    public function test_viewing_does_not_overwrite_an_existing_lead_source(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $opportunity = Opportunity::create(['requirement_id' => $requirement->id, 'status' => 'open', 'view_count' => 0]);
        $provider = Provider::factory()->create();

        (app(RecordProviderLead::class))($provider, $requirement, 'opportunity_match');

        (new RecordOpportunityView(app(RecordProviderLead::class)))($opportunity, $provider);

        $lead = ProviderLead::where('provider_id', $provider->id)->where('requirement_id', $requirement->id)->first();
        $this->assertSame('opportunity_match', $lead->source);
        $this->assertNotNull($lead->viewed_at);
    }
}
