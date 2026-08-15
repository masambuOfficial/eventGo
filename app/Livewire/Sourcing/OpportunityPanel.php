<?php

namespace App\Livewire\Sourcing;

use App\Domain\Attribution\Actions\RecordProviderImpression;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\CloseOpportunity;
use App\Domain\Sourcing\Actions\MatchProvidersToRequirement;
use App\Domain\Sourcing\Actions\PublishOpportunity;
use App\Domain\Sourcing\Actions\SendInvitation;
use App\Domain\Sourcing\Models\Invitation;
use App\Domain\Sourcing\Models\Opportunity;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Source this requirement')]
class OpportunityPanel extends Component
{
    public int $requirementId;

    public bool $budgetVisible = false;

    public ?int $budgetMinUgx = null;

    public ?int $budgetMaxUgx = null;

    public string $closesAt = '';

    public string $inviteMessage = '';

    public function mount(Requirement $requirement, MatchProvidersToRequirement $matchProviders, RecordProviderImpression $recordImpression): void
    {
        abort_unless($requirement->event->owner_user_id === auth()->id(), 403);

        $this->requirementId = $requirement->id;

        $opportunity = $requirement->opportunity;

        if ($opportunity) {
            $this->budgetVisible = $opportunity->budget_visible;
            $this->budgetMinUgx = $opportunity->budget_min_ugx;
            $this->budgetMaxUgx = $opportunity->budget_max_ugx;
            $this->closesAt = $opportunity->closes_at?->format('Y-m-d') ?? '';
        }

        foreach ($matchProviders($requirement) as $match) {
            $recordImpression($match['provider'], 'opportunity', $requirement, auth()->user());
        }
    }

    protected function requirement(): Requirement
    {
        return Requirement::with('event')->findOrFail($this->requirementId);
    }

    public function candidates(MatchProvidersToRequirement $matchProviders): Collection
    {
        $invitedProviderIds = Invitation::where('requirement_id', $this->requirementId)->pluck('provider_id');

        return $matchProviders($this->requirement())
            ->reject(fn (array $match) => $invitedProviderIds->contains($match['provider']->id))
            ->values();
    }

    public function invitations(): Collection
    {
        return Invitation::where('requirement_id', $this->requirementId)
            ->with('provider')
            ->latest('sent_at')
            ->get();
    }

    public function opportunity(): ?Opportunity
    {
        return Opportunity::where('requirement_id', $this->requirementId)->first();
    }

    public function publish(PublishOpportunity $publishOpportunity): void
    {
        $this->validate([
            'closesAt' => ['nullable', 'date', 'after:today'],
            'budgetMinUgx' => ['nullable', 'integer', 'min:0'],
            'budgetMaxUgx' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($this->budgetMinUgx !== null && $this->budgetMaxUgx !== null && $this->budgetMinUgx > $this->budgetMaxUgx) {
            $this->addError('budgetMaxUgx', 'Maximum budget must be at least the minimum.');

            return;
        }

        $publishOpportunity($this->requirement(), [
            'closes_at' => $this->closesAt ?: null,
            'budget_visible' => $this->budgetVisible,
            'budget_min_ugx' => $this->budgetMinUgx,
            'budget_max_ugx' => $this->budgetMaxUgx,
        ]);
    }

    public function close(CloseOpportunity $closeOpportunity): void
    {
        if ($opportunity = $this->opportunity()) {
            $closeOpportunity($opportunity);
        }
    }

    public function invite(int $providerId, SendInvitation $sendInvitation): void
    {
        $provider = Provider::findOrFail($providerId);

        $sendInvitation($this->requirement(), $provider, auth()->user(), $this->inviteMessage ?: null);

        $this->inviteMessage = '';
    }

    public function render()
    {
        return view('livewire.sourcing.opportunity-panel', [
            'requirement' => $this->requirement(),
            'candidates' => $this->candidates(app(MatchProvidersToRequirement::class)),
            'invitations' => $this->invitations(),
            'opportunity' => $this->opportunity(),
        ]);
    }
}
