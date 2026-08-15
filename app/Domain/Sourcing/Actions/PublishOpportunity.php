<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Attribution\Actions\RecordProviderLead;
use App\Domain\Events\Models\Requirement;
use App\Domain\Events\States\RequirementState\Sourcing;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Sourcing\Models\Opportunity;

/**
 * Publishing or inviting both move the requirement into `sourcing` —
 * architecture §5.1 notes the brief's two sourcing options only differ in
 * how offers arrive, so they converge on one state rather than forking the
 * model. Fans out to every matched provider immediately: each becomes a
 * provider_leads row and a bare notification. Real multi-channel delivery
 * is Phase 4 — this just writes the row the provider's inbox reads.
 */
class PublishOpportunity
{
    public function __construct(
        private MatchProvidersToRequirement $matchProviders,
        private RecordProviderLead $recordLead,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Requirement $requirement, array $data = []): Opportunity
    {
        if ($requirement->status->canTransitionTo(Sourcing::class)) {
            $requirement->status->transitionTo(Sourcing::class);
        }

        $opportunity = Opportunity::updateOrCreate(
            ['requirement_id' => $requirement->id],
            [
                'published_at' => now(),
                'closes_at' => $data['closes_at'] ?? null,
                'budget_visible' => $data['budget_visible'] ?? false,
                'budget_min_ugx' => $data['budget_min_ugx'] ?? null,
                'budget_max_ugx' => $data['budget_max_ugx'] ?? null,
                'status' => 'open',
            ]
        );

        foreach (($this->matchProviders)($requirement) as $match) {
            $provider = $match['provider'];

            ($this->recordLead)($provider, $requirement, 'opportunity_match');

            Notification::create([
                'user_id' => $provider->owner_user_id,
                'type' => 'opportunity_matched',
                'payload' => [
                    'requirement_id' => $requirement->id,
                    'opportunity_id' => $opportunity->id,
                    'requirement_title' => $requirement->title,
                ],
            ]);
        }

        return $opportunity;
    }
}
