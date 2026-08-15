<?php

namespace App\Domain\Bookings\States;

use App\Domain\Bookings\States\BookingState\Cancelled;
use App\Domain\Bookings\States\BookingState\Closed;
use App\Domain\Bookings\States\BookingState\Completed;
use App\Domain\Bookings\States\BookingState\Confirmed;
use App\Domain\Bookings\States\BookingState\InProgress;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/** Architecture §5.3 — light workspace, no gates. */
abstract class BookingState extends State
{
    protected static ?string $name = null;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Confirmed::class)
            ->registerState([
                Confirmed::class, InProgress::class, Completed::class, Closed::class, Cancelled::class,
            ])
            ->allowTransition(Confirmed::class, InProgress::class)
            ->allowTransition(Confirmed::class, Cancelled::class)
            ->allowTransition(InProgress::class, Completed::class)
            ->allowTransition(InProgress::class, Cancelled::class)
            ->allowTransition(Completed::class, Closed::class);
    }
}
