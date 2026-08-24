<?php

namespace App\Console\Commands;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\InProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §5.3: "Scheduler at T-7 days" moves a confirmed booking into
 * in_progress so the run-sheet view activates.
 */
class TransitionBookingsToInProgress extends Command
{
    protected $signature = 'bookings:transition-in-progress';

    protected $description = 'Move confirmed bookings into in_progress 7 days before the event starts';

    public function handle(): int
    {
        Booking::query()
            ->where('status', 'confirmed')
            ->whereHas('event', fn ($query) => $query->where('starts_at', '<=', now()->addDays(7)))
            ->with('event')
            ->each(function (Booking $booking) {
                try {
                    if ($booking->status->canTransitionTo(InProgress::class)) {
                        $booking->status->transitionTo(InProgress::class);
                        $booking->save();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not transition booking to in_progress.', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
