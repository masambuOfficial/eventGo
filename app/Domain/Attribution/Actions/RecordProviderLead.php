<?php

namespace App\Domain\Attribution\Actions;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;

/**
 * The impression → notification → view → offer → outcome chain architecture
 * §8.3 calls the evidence for the renewal conversation. `source` is fixed at
 * first sight — a provider who later searches their way to a lead they were
 * already invited to keeps the `direct_invitation` attribution, not `search`.
 */
class RecordProviderLead
{
    public function __invoke(Provider $provider, Requirement $requirement, string $source): ProviderLead
    {
        $lead = ProviderLead::firstOrNew([
            'provider_id' => $provider->id,
            'requirement_id' => $requirement->id,
        ]);

        if (! $lead->exists) {
            $lead->source = $source;
            $lead->outcome = 'pending';
        }

        if (! $lead->notified_at) {
            $lead->notified_at = now();
        }

        $lead->save();

        return $lead;
    }
}
