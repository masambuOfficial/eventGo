<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class Awarded extends RequirementState
{
    protected static ?string $name = 'awarded';
}
