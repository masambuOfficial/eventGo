<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Opportunity;

class CloseOpportunity
{
    public function __invoke(Opportunity $opportunity): void
    {
        $opportunity->forceFill(['status' => 'closed'])->save();
    }
}
