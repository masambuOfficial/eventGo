<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Attribution\Actions\RecordProviderLead;
use App\Domain\Attribution\Actions\TouchConnection;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Sourcing;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Invitation;
use App\Models\User;

class SendInvitation
{
    public function __construct(
        private RecordProviderLead $recordLead,
        private TouchConnection $touchConnection,
    ) {}

    public function __invoke(Requirement $requirement, Provider $provider, User $invitedBy, ?string $message = null): Invitation
    {
        if ($requirement->status->canTransitionTo(Sourcing::class)) {
            $requirement->status->transitionTo(Sourcing::class);
        }

        $invitation = Invitation::updateOrCreate(
            ['requirement_id' => $requirement->id, 'provider_id' => $provider->id],
            [
                'invited_by_user_id' => $invitedBy->id,
                'message' => $message,
                'sent_at' => now(),
                'status' => 'sent',
            ]
        );

        ($this->recordLead)($provider, $requirement, 'direct_invitation');
        ($this->touchConnection)($invitedBy, $provider, 'direct_invitation', $requirement->event);

        Notification::create([
            'user_id' => $provider->owner_user_id,
            'type' => 'invitation_received',
            'payload' => [
                'requirement_id' => $requirement->id,
                'invitation_id' => $invitation->id,
                'requirement_title' => $requirement->title,
            ],
        ]);

        return $invitation;
    }
}
