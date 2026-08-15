<?php

namespace App\Domain\Bookings\States\BookingState;

use App\Domain\Bookings\States\BookingState;

class Completed extends BookingState
{
    protected static ?string $name = 'completed';
}
