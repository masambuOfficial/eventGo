<?php

namespace App\Domain\Messaging\Listeners;

use App\Domain\Messaging\Models\Thread;
use App\Domain\Sourcing\Events\OfferAccepted;

/**
 * Opens the workspace thread the moment a booking exists, per architecture
 * §9.2's worked example ("...open the thread..." among OfferAccepted's
 * listener responsibilities). Lives in Messaging, not Bookings, matching how
 * `UpdateLeadsAndConnectionsOnAcceptance` lives in Attribution even though
 * OfferAccepted is a Sourcing event — the listener sits with the domain
 * whose data it writes.
 */
class OpenBookingThread
{
    public function handle(OfferAccepted $event): void
    {
        $booking = $event->booking;
        $organiserId = $booking->event->owner_user_id;
        $providerOwnerId = $booking->provider->owner_user_id;

        $thread = Thread::create([
            'subject_type' => 'booking',
            'subject_id' => $booking->id,
        ]);

        $thread->participants()->attach([
            $organiserId => ['role' => 'organiser'],
            $providerOwnerId => ['role' => 'provider'],
        ]);
    }
}
