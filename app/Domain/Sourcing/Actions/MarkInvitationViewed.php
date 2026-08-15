<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Invitation;

class MarkInvitationViewed
{
    public function __invoke(Invitation $invitation): Invitation
    {
        if (! $invitation->viewed_at) {
            $invitation->forceFill([
                'viewed_at' => now(),
                'status' => $invitation->status === 'sent' ? 'viewed' : $invitation->status,
            ])->save();
        }

        return $invitation;
    }
}
