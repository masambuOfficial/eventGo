<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingAmendment;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Append-only log, architecture §5.4 — no approval state machine, Event Go
 * records what the parties say they agreed and does not enforce it. No
 * status guard: an amendment after completion is still a legitimate record.
 */
class RecordAmendment
{
    public function __construct(private NotifyUser $notifyUser)
    {
    }

    public function __invoke(Booking $booking, User $actor, int $newTotalUgx, ?string $note): BookingAmendment
    {
        $amendment = DB::transaction(function () use ($booking, $actor, $newTotalUgx, $note) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            $amendment = BookingAmendment::create([
                'booking_id' => $locked->id,
                'changed_by_user_id' => $actor->id,
                'previous_total_ugx' => $locked->agreed_total_ugx,
                'new_total_ugx' => $newTotalUgx,
                'note' => $note,
            ]);

            $locked->forceFill(['agreed_total_ugx' => $newTotalUgx])->save();

            return $amendment;
        });

        $side = $booking->viewerSide($actor);
        $otherSide = $side === 'organiser' ? $booking->provider->owner : $booking->event->owner;

        ($this->notifyUser)($otherSide, 'booking_amended', [
            'booking_id' => $booking->id,
            'previous_total_ugx' => $amendment->previous_total_ugx,
            'new_total_ugx' => $amendment->new_total_ugx,
        ]);

        return $amendment;
    }
}
