<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Invitation;

class RespondToInvitation
{
    public function __invoke(Invitation $invitation, bool $willSubmitOffer): Invitation
    {
        $invitation->forceFill([
            'responded_at' => now(),
            'status' => $willSubmitOffer ? 'responded' : 'declined',
        ])->save();

        return $invitation;
    }
}
