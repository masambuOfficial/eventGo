<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Actions\ForceCompleteBooking;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Completed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForceCompleteBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_completes_a_booking_already_in_progress(): void
    {
        $booking = Booking::factory()->create(['status' => 'in_progress']);

        (new ForceCompleteBooking)($booking);

        $booking->refresh();
        $this->assertInstanceOf(Completed::class, $booking->status);
        $this->assertNotNull($booking->organiser_completed_at);
        $this->assertNotNull($booking->provider_completed_at);
    }

    public function test_self_heals_through_in_progress_when_still_confirmed(): void
    {
        $booking = Booking::factory()->create(['status' => 'confirmed']);

        (new ForceCompleteBooking)($booking);

        $booking->refresh();
        $this->assertInstanceOf(Completed::class, $booking->status);
    }
}
