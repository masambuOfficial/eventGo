<?php

namespace Tests\Feature\Bookings;

use App\Domain\Bookings\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AutoCloseCompletedBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_closes_bookings_completed_more_than_thirty_days_ago(): void
    {
        $staleBooking = Booking::factory()->create([
            'status' => 'completed',
            'organiser_completed_at' => now()->subDays(40),
            'provider_completed_at' => now()->subDays(35),
        ]);

        $recentBooking = Booking::factory()->create([
            'status' => 'completed',
            'organiser_completed_at' => now()->subDays(5),
            'provider_completed_at' => now()->subDays(5),
        ]);

        Artisan::call('bookings:auto-close');

        $this->assertSame('closed', $staleBooking->fresh()->status->getValue());
        $this->assertSame('completed', $recentBooking->fresh()->status->getValue());
    }
}
