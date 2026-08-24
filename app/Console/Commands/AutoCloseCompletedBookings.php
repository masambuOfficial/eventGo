<?php

namespace App\Console\Commands;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Closed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * No trigger for completed -> closed is specified anywhere in architecture
 * §5.3 — the transition is legally registered in BookingState.php but was
 * left unreachable. Rather than leave a state the code already allows
 * permanently dead, this closes bookkeeping out 30 days after both sides
 * marked complete. Zero specified side effects, so it's a pure status flip;
 * revisit if a later phase gives `closed` an actual functional gate.
 */
class AutoCloseCompletedBookings extends Command
{
    protected $signature = 'bookings:auto-close';

    protected $description = 'Close out completed bookings 30 days after both sides marked complete';

    public function handle(): int
    {
        Booking::query()
            ->where('status', 'completed')
            ->whereRaw('GREATEST(organiser_completed_at, provider_completed_at) <= ?', [now()->subDays(30)])
            ->each(function (Booking $booking) {
                try {
                    if ($booking->status->canTransitionTo(Closed::class)) {
                        $booking->status->transitionTo(Closed::class);
                        $booking->save();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not auto-close completed booking.', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
