<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TransitionBookingsToInProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_transitions_bookings_within_seven_days_of_the_event(): void
    {
        $dueEvent = Event::factory()->create(['starts_at' => now()->addDays(3)]);
        $dueBooking = Booking::factory()->create(['event_id' => $dueEvent->id, 'status' => 'confirmed']);

        $notDueEvent = Event::factory()->create(['starts_at' => now()->addDays(20)]);
        $notDueBooking = Booking::factory()->create(['event_id' => $notDueEvent->id, 'status' => 'confirmed']);

        Artisan::call('bookings:transition-in-progress');

        $this->assertSame('in_progress', $dueBooking->fresh()->status->getValue());
        $this->assertSame('confirmed', $notDueBooking->fresh()->status->getValue());
    }
}
