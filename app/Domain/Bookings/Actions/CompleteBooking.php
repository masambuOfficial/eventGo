<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Architecture §5.3: "Both sides mark complete... reviews unlock for both
 * parties." Each side marks independently and idempotently; the booking
 * only transitions once both timestamps are set.
 */
class CompleteBooking
{
    public function __construct(private NotifyUser $notifyUser)
    {
    }

    public function __invoke(Booking $booking, User $actor): Booking
    {
        $notifyOther = false;

        $booking = DB::transaction(function () use ($booking, $actor, &$notifyOther) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            $side = $locked->viewerSide($actor);
            abort_unless($side !== null, 403);

            if ($side === 'organiser' && $locked->organiser_completed_at === null) {
                $locked->organiser_completed_at = now();
                $notifyOther = $locked->provider_completed_at === null;
            } elseif ($side === 'provider' && $locked->provider_completed_at === null) {
                $locked->provider_completed_at = now();
                $notifyOther = $locked->organiser_completed_at === null;
            }

            if ($locked->organiser_completed_at && $locked->provider_completed_at
                && $locked->status->canTransitionTo(Completed::class)) {
                $locked->status->transitionTo(Completed::class);
            }

            $locked->save();

            return $locked;
        });

        if ($notifyOther) {
            $side = $booking->viewerSide($actor);
            $other = $side === 'organiser' ? $booking->provider->owner : $booking->event->owner;

            ($this->notifyUser)($other, 'booking_marked_complete', [
                'booking_id' => $booking->id,
            ]);
        }

        return $booking;
    }
}
