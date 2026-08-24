<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoCompleteOverdueBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_force_completes_bookings_fourteen_days_after_the_event_ended(): void
    {
        $overdueEvent = Event::factory()->create(['starts_at' => now()->subDays(20)]);
        $overdueBooking = Booking::factory()->create(['event_id' => $overdueEvent->id, 'status' => 'in_progress']);

        $recentEvent = Event::factory()->create(['starts_at' => now()->subDays(2)]);
        $recentBooking = Booking::factory()->create(['event_id' => $recentEvent->id, 'status' => 'in_progress']);

        Artisan::call('bookings:auto-complete');

        $this->assertSame('completed', $overdueBooking->fresh()->status->getValue());
        $this->assertSame('in_progress', $recentBooking->fresh()->status->getValue());
    }
}
