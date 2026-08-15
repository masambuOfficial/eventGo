<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\MarkInvitationViewed;
use App\Domain\Sourcing\Actions\RespondToInvitation;
use App\Domain\Sourcing\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationResponseTest extends TestCase
{
    use RefreshDatabase;

    private function invitation(): Invitation
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);

        return Invitation::create([
            'requirement_id' => $requirement->id,
            'provider_id' => Provider::factory()->create()->id,
            'invited_by_user_id' => User::factory()->create()->id,
            'sent_at' => now(),
            'status' => 'sent',
        ]);
    }

    public function test_marking_viewed_moves_status_from_sent_to_viewed_once(): void
    {
        $invitation = $this->invitation();

        (new MarkInvitationViewed)($invitation);
        $firstViewedAt = $invitation->fresh()->viewed_at;

        $this->assertSame('viewed', $invitation->fresh()->status);
        $this->assertNotNull($firstViewedAt);

        (new MarkInvitationViewed)($invitation->fresh());

        $this->assertEquals($firstViewedAt, $invitation->fresh()->viewed_at);
    }

    public function test_responding_accepted_sets_responded_status(): void
    {
        $invitation = $this->invitation();

        (new RespondToInvitation)($invitation, true);

        $this->assertSame('responded', $invitation->fresh()->status);
        $this->assertNotNull($invitation->fresh()->responded_at);
    }

    public function test_responding_declined_sets_declined_status(): void
    {
        $invitation = $this->invitation();

        (new RespondToInvitation)($invitation, false);

        $this->assertSame('declined', $invitation->fresh()->status);
    }
}
