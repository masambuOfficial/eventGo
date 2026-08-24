<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Bookings\States\BookingState\InProgress;
use Illuminate\Support\Facades\DB;

/**
 * The 14-day-after-event auto-complete timeout (architecture §5.3). Only
 * called by the scheduled command — `CompleteBooking` handles the normal,
 * both-sides-mark-complete path.
 *
 * `BookingState` only allows InProgress -> Completed, not Confirmed ->
 * Completed directly, so if the T-7 transition-in-progress job hasn't run
 * yet for this booking (e.g. it was created within the 7-day window), this
 * self-heals through InProgress first rather than assuming that job already
 * fired. That's load-bearing, not defensive fluff.
 */
class ForceCompleteBooking
{
    public function __invoke(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if ($locked->status->canTransitionTo(InProgress::class)) {
                $locked->status->transitionTo(InProgress::class);
            }

            $locked->organiser_completed_at ??= now();
            $locked->provider_completed_at ??= now();

            if ($locked->status->canTransitionTo(Completed::class)) {
                $locked->status->transitionTo(Completed::class);
            }

            $locked->save();

            return $locked;
        });
    }
}
