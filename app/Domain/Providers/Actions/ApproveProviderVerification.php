<?php

namespace App\Domain\Providers\Actions;

use App\Domain\Providers\Models\ProviderVerification;
use App\Models\User;

class ApproveProviderVerification
{
    public function __invoke(ProviderVerification $verification, User $reviewer, ?string $notes = null): void
    {
        $verification->forceFill([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => $notes ?: $verification->notes,
        ])->save();

        $provider = $verification->provider;

        if ($verification->tier > $provider->verification_tier) {
            $provider->forceFill(['verification_tier' => $verification->tier])->save();
        }

        if ($verification->evidence_type === 'social_page') {
            $provider->socialAccounts()
                ->where('profile_url', $verification->evidence_path)
                ->update(['verified_at' => now()]);
        }
    }
}
