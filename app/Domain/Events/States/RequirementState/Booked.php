<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class Booked extends RequirementState
{
    protected static ?string $name = 'booked';
}
