<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Actions\CompleteBooking;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Bookings\States\BookingState\InProgress;
use App\Domain\Events\Models\Event;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteBookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeBooking(): array
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'status' => 'in_progress',
        ]);

        return [$booking, $organiser, $provider->owner];
    }

    public function test_one_sided_completion_does_not_transition(): void
    {
        [$booking, $organiser] = $this->makeBooking();

        (new CompleteBooking(new NotifyUser))($booking, $organiser);

        $booking->refresh();
        $this->assertNotNull($booking->organiser_completed_at);
        $this->assertNull($booking->provider_completed_at);
        $this->assertInstanceOf(InProgress::class, $booking->status);
    }

    public function test_both_sides_completing_transitions_to_completed(): void
    {
        [$booking, $organiser, $providerOwner] = $this->makeBooking();

        $completeBooking = new CompleteBooking(new NotifyUser);
        $completeBooking($booking, $organiser);
        $completeBooking($booking->fresh(), $providerOwner);

        $booking->refresh();
        $this->assertNotNull($booking->organiser_completed_at);
        $this->assertNotNull($booking->provider_completed_at);
        $this->assertInstanceOf(Completed::class, $booking->status);
    }

    public function test_recalling_from_an_already_completed_side_is_a_no_op(): void
    {
        [$booking, $organiser] = $this->makeBooking();

        $completeBooking = new CompleteBooking(new NotifyUser);
        $completeBooking($booking, $organiser);
        $firstTimestamp = $booking->fresh()->organiser_completed_at;

        $completeBooking($booking->fresh(), $organiser);

        $this->assertEquals($firstTimestamp, $booking->fresh()->organiser_completed_at);
        $this->assertNull($booking->fresh()->provider_completed_at);
    }
}
