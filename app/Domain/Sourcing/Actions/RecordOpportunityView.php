<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Attribution\Actions\RecordProviderLead;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Opportunity;

/**
 * A provider opening an opportunity in their own feed is not an
 * impression — provider_impressions tracks a provider being shown to
 * someone else (architecture §8.3). This updates the provider_leads chain
 * instead: `search` if they found it themselves rather than being matched
 * or invited (RecordProviderLead keeps the original `source` if a lead
 * already exists).
 */
class RecordOpportunityView
{
    public function __construct(private RecordProviderLead $recordLead)
    {
    }

    public function __invoke(Opportunity $opportunity, Provider $provider): void
    {
        $opportunity->increment('view_count');

        $lead = ($this->recordLead)($provider, $opportunity->requirement, 'search');

        if (! $lead->viewed_at) {
            $lead->forceFill(['viewed_at' => now()])->save();
        }
    }
}
