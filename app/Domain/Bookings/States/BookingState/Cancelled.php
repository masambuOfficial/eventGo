<?php

namespace App\Domain\Bookings\States\BookingState;

use App\Domain\Bookings\States\BookingState;

class Cancelled extends BookingState
{
    protected static ?string $name = 'cancelled';
}
