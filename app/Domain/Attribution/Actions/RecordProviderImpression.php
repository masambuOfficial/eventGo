<?php

namespace App\Domain\Attribution\Actions;

use App\Domain\Attribution\Models\ProviderImpression;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Models\User;

/**
 * Records that a provider was shown to someone — not that a provider viewed
 * something. Architecture §8.3: this feeds "Profile views" / "Search
 * appearances" on the provider ROI dashboard (Phase 5). Whether a provider
 * viewed an opportunity matched to them is tracked separately, on
 * provider_leads.viewed_at.
 */
class RecordProviderImpression
{
    public function __invoke(
        Provider $provider,
        string $context,
        ?Requirement $requirement = null,
        ?User $viewer = null,
        bool $wasFeatured = false,
    ): ProviderImpression {
        return ProviderImpression::create([
            'provider_id' => $provider->id,
            'context' => $context,
            'requirement_id' => $requirement?->id,
            'viewer_user_id' => $viewer?->id,
            'was_featured' => $wasFeatured,
        ]);
    }
}
