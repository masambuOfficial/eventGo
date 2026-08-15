<?php

namespace App\Livewire\Providers\Opportunities;

use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\MarkInvitationViewed;
use App\Domain\Sourcing\Actions\RespondToInvitation;
use App\Domain\Sourcing\Models\Invitation;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Invitations')]
class Invitations extends Component
{
    public int $providerId;

    public function mount(): void
    {
        $provider = Provider::where('owner_user_id', auth()->id())->first();

        if (! $provider) {
            $this->redirectRoute('provider.onboarding');

            return;
        }

        $this->providerId = $provider->id;
    }

    public function invitations(): Collection
    {
        return Invitation::where('provider_id', $this->providerId)
            ->with(['requirement.event', 'requirement.category', 'invitedBy'])
            ->latest('sent_at')
            ->get();
    }

    protected function ownedInvitation(int $invitationId): Invitation
    {
        return Invitation::where('id', $invitationId)
            ->where('provider_id', $this->providerId)
            ->firstOrFail();
    }

    public function view(int $invitationId, MarkInvitationViewed $markViewed): void
    {
        $markViewed($this->ownedInvitation($invitationId));
    }

    public function respond(int $invitationId, bool $willSubmitOffer, RespondToInvitation $respond): void
    {
        $respond($this->ownedInvitation($invitationId), $willSubmitOffer);
    }

    public function render()
    {
        return view('livewire.providers.opportunities.invitations', [
            'invitations' => $this->invitations(),
        ]);
    }
}
