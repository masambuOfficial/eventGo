<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class OffersReceived extends RequirementState
{
    protected static ?string $name = 'offers_received';
}
