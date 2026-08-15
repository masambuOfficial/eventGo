<?php

namespace App\Domain\Bookings\States\BookingState;

use App\Domain\Bookings\States\BookingState;

class Closed extends BookingState
{
    protected static ?string $name = 'closed';
}
