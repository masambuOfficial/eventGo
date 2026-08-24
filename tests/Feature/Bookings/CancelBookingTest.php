<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Actions\CancelBooking;
use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Cancelled;
use App\Domain\Events\Models\Event;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_organiser_can_cancel_a_confirmed_booking_and_release_availability(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create([
            'owner_user_id' => $organiser->id,
            'starts_at' => now()->addMonth()->startOfDay(),
        ]);
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'status' => 'confirmed',
        ]);

        ProviderAvailability::create([
            'provider_id' => $provider->id,
            'date' => $event->starts_at->toDateString(),
            'capacity_total' => 1,
            'capacity_used' => 1,
        ]);

        (new CancelBooking(new NotifyUser))($booking, $organiser, 'Client changed plans');

        $booking->refresh();
        $this->assertInstanceOf(Cancelled::class, $booking->status);
        $this->assertSame('organiser', $booking->cancelled_by_side);
        $this->assertNotNull($booking->cancelled_at);

        $availability = ProviderAvailability::where('provider_id', $provider->id)
            ->where('date', $event->starts_at->toDateString())
            ->first();
        $this->assertSame(0, $availability->capacity_used);
    }

    public function test_cancelling_a_completed_booking_is_rejected(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create(['owner_user_id' => $organiser->id]);
        $booking = Booking::factory()->create(['event_id' => $event->id, 'status' => 'completed']);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        (new CancelBooking(new NotifyUser))($booking, $organiser, 'Too late');
    }

    public function test_capacity_used_is_floored_at_zero(): void
    {
        $organiser = User::factory()->create();
        $event = Event::factory()->create([
            'owner_user_id' => $organiser->id,
            'starts_at' => now()->addMonth()->startOfDay(),
        ]);
        $provider = Provider::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'status' => 'confirmed',
        ]);

        ProviderAvailability::create([
            'provider_id' => $provider->id,
            'date' => $event->starts_at->toDateString(),
            'capacity_total' => 1,
            'capacity_used' => 0,
        ]);

        (new CancelBooking(new NotifyUser))($booking, $organiser, 'Already zero');

        $availability = ProviderAvailability::where('provider_id', $provider->id)
            ->where('date', $event->starts_at->toDateString())
            ->first();
        $this->assertSame(0, $availability->capacity_used);
    }
}
