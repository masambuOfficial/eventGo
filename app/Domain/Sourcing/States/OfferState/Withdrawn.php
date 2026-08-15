<?php

namespace App\Domain\Sourcing\States\OfferState;

use App\Domain\Sourcing\States\OfferState;

class Withdrawn extends OfferState
{
    protected static ?string $name = 'withdrawn';
}
