<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class Dropped extends RequirementState
{
    protected static ?string $name = 'dropped';
}
