<?php

namespace App\Domain\Bookings\States\BookingState;

use App\Domain\Bookings\States\BookingState;

class InProgress extends BookingState
{
    protected static ?string $name = 'in_progress';
}
