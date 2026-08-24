<?php

namespace App\Console\Commands;

use App\Domain\Bookings\Actions\ForceCompleteBooking;
use App\Domain\Bookings\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §5.3: "Both sides mark complete (or 14-day timeout after the
 * event date)". Catches bookings nobody marked complete.
 */
class AutoCompleteOverdueBookings extends Command
{
    protected $signature = 'bookings:auto-complete';

    protected $description = 'Force-complete bookings 14 days after the event ended, if neither side marked complete';

    public function handle(ForceCompleteBooking $forceCompleteBooking): int
    {
        Booking::query()
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->whereHas('event', function ($query) {
                $query->whereRaw('COALESCE(ends_at, starts_at) <= ?', [now()->subDays(14)]);
            })
            ->with('event')
            ->each(function (Booking $booking) use ($forceCompleteBooking) {
                try {
                    $forceCompleteBooking($booking);
                } catch (\Throwable $e) {
                    Log::warning('Could not auto-complete overdue booking.', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
