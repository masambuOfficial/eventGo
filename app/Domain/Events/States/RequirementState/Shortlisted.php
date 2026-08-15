<?php

namespace App\Domain\Events\States\RequirementState;

use App\Domain\Events\States\RequirementState;

class Shortlisted extends RequirementState
{
    protected static ?string $name = 'shortlisted';
}
