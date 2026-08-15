<?php

namespace App\Domain\Bookings\States\BookingState;

use App\Domain\Bookings\States\BookingState;

class Confirmed extends BookingState
{
    protected static ?string $name = 'confirmed';
}
