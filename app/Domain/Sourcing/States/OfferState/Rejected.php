<?php

namespace App\Domain\Sourcing\States\OfferState;

use App\Domain\Sourcing\States\OfferState;

class Rejected extends OfferState
{
    protected static ?string $name = 'rejected';
}
