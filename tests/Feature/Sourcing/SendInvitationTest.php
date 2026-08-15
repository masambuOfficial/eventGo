<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Attribution\Models\Connection;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Sourcing;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\SendInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_an_invitation_creates_a_lead_a_connection_and_a_notification(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $organiser->id]);
        $requirement = Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => ServiceCategory::factory(),
            'status' => 'open',
        ]);
        $provider = Provider::factory()->create();

        $invitation = (new SendInvitation(
            app(\App\Domain\Attribution\Actions\RecordProviderLead::class),
            app(\App\Domain\Attribution\Actions\TouchConnection::class),
        ))($requirement, $provider, $organiser, 'Please quote for this.');

        $this->assertSame('sent', $invitation->status);
        $this->assertInstanceOf(Sourcing::class, $requirement->fresh()->status);

        $this->assertDatabaseHas('provider_leads', [
            'provider_id' => $provider->id,
            'requirement_id' => $requirement->id,
            'source' => 'direct_invitation',
        ]);

        $connection = Connection::where('organiser_user_id', $organiser->id)
            ->where('provider_id', $provider->id)
            ->first();

        $this->assertNotNull($connection);
        $this->assertSame('direct_invitation', $connection->origin);
        $this->assertSame('contacted', $connection->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->owner_user_id,
            'type' => 'invitation_received',
        ]);
    }

    public function test_inviting_the_same_provider_twice_touches_rather_than_duplicates_the_connection(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $organiser->id]);
        $requirementA = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'open']);
        $requirementB = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory(), 'status' => 'open']);
        $provider = Provider::factory()->create();

        $action = new SendInvitation(
            app(\App\Domain\Attribution\Actions\RecordProviderLead::class),
            app(\App\Domain\Attribution\Actions\TouchConnection::class),
        );

        $action($requirementA, $provider, $organiser);
        $action($requirementB, $provider, $organiser);

        $this->assertSame(1, Connection::where('organiser_user_id', $organiser->id)->where('provider_id', $provider->id)->count());
    }
}
