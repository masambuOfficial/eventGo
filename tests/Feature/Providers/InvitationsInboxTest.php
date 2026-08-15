<?php

namespace Tests\Feature\Providers;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Invitation;
use App\Livewire\Providers\Opportunities\Invitations;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvitationsInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_provider_can_respond_to_their_own_invitation(): void
    {
        $provider = Provider::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);

        $invitation = Invitation::create([
            'requirement_id' => $requirement->id,
            'provider_id' => $provider->id,
            'invited_by_user_id' => User::factory()->create()->id,
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        Livewire::actingAs($provider->owner)
            ->test(Invitations::class)
            ->call('respond', $invitation->id, true);

        $this->assertSame('responded', $invitation->fresh()->status);
    }

    public function test_a_provider_cannot_respond_to_someone_elses_invitation(): void
    {
        $provider = Provider::factory()->create();
        $otherProvider = Provider::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);

        $invitation = Invitation::create([
            'requirement_id' => $requirement->id,
            'provider_id' => $otherProvider->id,
            'invited_by_user_id' => User::factory()->create()->id,
            'sent_at' => now(),
            'status' => 'sent',
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($provider->owner)
            ->test(Invitations::class)
            ->call('respond', $invitation->id, true);
    }
}
