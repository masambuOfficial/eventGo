<?php

namespace App\Domain\Sourcing\Events;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Sourcing\Models\Offer;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched by Bookings\AcceptOffer once the booking exists. Listeners
 * handle everything that doesn't need to be atomic with the acceptance
 * itself — architecture §9.2: reserve availability, release contacts,
 * update attribution, seed workspace tasks, open the thread.
 */
class OfferAccepted
{
    use Dispatchable;

    public function __construct(
        public readonly Offer $offer,
        public readonly Booking $booking,
    ) {
    }
}
