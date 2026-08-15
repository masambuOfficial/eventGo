<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Cancelled;
use App\Domain\Bookings\States\BookingState\Closed;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Bookings\States\BookingState\Confirmed;
use App\Domain\Bookings\States\BookingState\InProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Tests\TestCase;

class BookingStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_defaults_to_confirmed(): void
    {
        $booking = Booking::factory()->create(['status' => null]);

        $this->assertInstanceOf(Confirmed::class, $booking->status);
    }

    public function test_booking_follows_the_happy_path_to_closed(): void
    {
        $booking = Booking::factory()->create(['status' => Confirmed::class]);

        $booking->status->transitionTo(InProgress::class);
        $booking->status->transitionTo(Completed::class);
        $booking->status->transitionTo(Closed::class);

        $this->assertInstanceOf(Closed::class, $booking->fresh()->status);
    }

    public function test_booking_can_be_cancelled_from_confirmed_or_in_progress(): void
    {
        $booking = Booking::factory()->create(['status' => Confirmed::class]);
        $booking->status->transitionTo(Cancelled::class);

        $this->assertInstanceOf(Cancelled::class, $booking->fresh()->status);
    }

    public function test_closed_booking_cannot_be_cancelled(): void
    {
        $booking = Booking::factory()->create(['status' => Confirmed::class]);
        $booking->status->transitionTo(InProgress::class);
        $booking->status->transitionTo(Completed::class);
        $booking->status->transitionTo(Closed::class);

        $this->expectException(CouldNotPerformTransition::class);

        $booking->status->transitionTo(Cancelled::class);
    }
}
