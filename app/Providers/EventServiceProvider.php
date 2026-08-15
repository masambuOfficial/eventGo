<?php

namespace App\Providers;

use App\Domain\Attribution\Listeners\UpdateLeadsAndConnectionsOnAcceptance;
use App\Domain\Bookings\Listeners\ReleaseContactDetails;
use App\Domain\Bookings\Listeners\ReserveProviderAvailability;
use App\Domain\Sourcing\Events\OfferAccepted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OfferAccepted::class => [
            ReserveProviderAvailability::class,
            ReleaseContactDetails::class,
            UpdateLeadsAndConnectionsOnAcceptance::class,
        ],
    ];
}
