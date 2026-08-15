<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class Draft extends RequirementState
{
    protected static ?string $name = 'draft';
}
