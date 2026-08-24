<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Actions\RecordAmendment;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordAmendmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_amendments_chain_the_previous_total_across_calls(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'agreed_total_ugx' => 5_000_000,
            'original_total_ugx' => 5_000_000,
        ]);

        $recordAmendment = new RecordAmendment(new NotifyUser);

        $first = $recordAmendment($booking, $organiser, 6_000_000, 'Guest count increased');

        $this->assertSame(5_000_000, $first->previous_total_ugx);
        $this->assertSame(6_000_000, $first->new_total_ugx);
        $this->assertSame(6_000_000, $booking->fresh()->agreed_total_ugx);

        $second = $recordAmendment($booking->fresh(), $organiser, 6_500_000, 'Added a service');

        $this->assertSame(6_000_000, $second->previous_total_ugx);
        $this->assertSame(6_500_000, $second->new_total_ugx);
        $this->assertSame(6_500_000, $booking->fresh()->agreed_total_ugx);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->owner_user_id,
            'type' => 'booking_amended',
        ]);
    }
}
