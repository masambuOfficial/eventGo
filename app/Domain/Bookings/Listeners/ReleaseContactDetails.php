<?php

namespace App\Domain\Bookings\Listeners;

use App\Domain\Sourcing\Events\OfferAccepted;

/**
 * Contacts release on acceptance, not later — architecture §5.3. Masking
 * only protects the browsing/offer-submission phase against bulk scraping;
 * once a booking exists it is pure friction.
 */
class ReleaseContactDetails
{
    public function handle(OfferAccepted $event): void
    {
        $event->booking->forceFill(['contacts_released_at' => now()])->save();
    }
}
