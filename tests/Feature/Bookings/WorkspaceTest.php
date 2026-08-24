<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use App\Livewire\Bookings\Workspace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_third_party_cannot_view_the_workspace(): void
    {
        $stranger = User::factory()->create();
        $booking = Booking::factory()->create();

        Livewire::actingAs($stranger)->test(Workspace::class, ['booking' => $booking])->assertStatus(403);
    }

    public function test_the_organiser_and_the_provider_can_both_view_the_workspace(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create(['event_id' => $event->id, 'provider_id' => $provider->id]);

        Livewire::actingAs($organiser)->test(Workspace::class, ['booking' => $booking])->assertOk();
        Livewire::actingAs($provider->owner)->test(Workspace::class, ['booking' => $booking])->assertOk();
    }
}
