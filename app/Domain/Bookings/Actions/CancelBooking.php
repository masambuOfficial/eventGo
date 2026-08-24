<?php

namespace App\Domain\Bookings\Actions;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\States\BookingState\Cancelled;
use App\Domain\Notifications\Actions\NotifyUser;
use App\Domain\Providers\Models\ProviderAvailability;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §5.3: "Either side, with a note... No policy applied, no
 * refund calculated — the parties settle between themselves." Availability
 * release runs after the state transition commits, not inside it — mirrors
 * ReserveProviderAvailability's reasoning: a capacity hiccup must never
 * roll back the cancellation itself.
 */
class CancelBooking
{
    public function __construct(private NotifyUser $notifyUser)
    {
    }

    public function __invoke(Booking $booking, User $actor, string $note): Booking
    {
        $booking = DB::transaction(function () use ($booking, $actor, $note) {
            $locked = Booking::whereKey($booking->id)->lockForUpdate()->first();

            $side = $locked->viewerSide($actor);
            abort_unless($side !== null, 403);
            abort_unless($locked->status->canTransitionTo(Cancelled::class), 422);

            $locked->status->transitionTo(Cancelled::class);
            $locked->forceFill([
                'cancelled_at' => now(),
                'cancelled_by_side' => $side,
                'cancellation_note' => $note,
            ])->save();

            return $locked;
        });

        $this->releaseAvailability($booking);

        $side = $booking->viewerSide($actor);
        $other = $side === 'organiser' ? $booking->provider->owner : $booking->event->owner;

        ($this->notifyUser)($other, 'booking_cancelled', [
            'booking_id' => $booking->id,
            'cancelled_by_side' => $side,
            'note' => $note,
        ]);

        return $booking;
    }

    private function releaseAvailability(Booking $booking): void
    {
        $event = $booking->event;
        $start = $event->starts_at->copy()->startOfDay();
        $end = ($event->ends_at ?? $event->starts_at)->copy()->startOfDay();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $this->releaseDate($booking->provider_id, $date->toDateString());
        }
    }

    private function releaseDate(int $providerId, string $date): void
    {
        try {
            DB::transaction(function () use ($providerId, $date) {
                $availability = ProviderAvailability::where('provider_id', $providerId)
                    ->where('date', $date)
                    ->lockForUpdate()
                    ->first();

                if ($availability && $availability->capacity_used > 0) {
                    $availability->decrement('capacity_used');
                }
            });
        } catch (\Throwable $e) {
            Log::warning('Could not release provider availability after booking cancellation.', [
                'provider_id' => $providerId,
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
