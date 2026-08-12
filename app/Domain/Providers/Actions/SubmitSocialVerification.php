<?php

namespace App\Domain\Providers\Actions;

use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderSocialAccount;
use App\Domain\Providers\Models\ProviderVerification;

class SubmitSocialVerification
{
    public function __invoke(Provider $provider, array $data): ProviderVerification
    {
        $account = ProviderSocialAccount::updateOrCreate(
            ['provider_id' => $provider->id, 'platform' => $data['platform']],
            [
                'handle' => $data['handle'],
                'profile_url' => $data['profile_url'],
                'follower_count' => $data['follower_count'] ?? null,
            ]
        );

        return ProviderVerification::create([
            'provider_id' => $provider->id,
            'tier' => 1,
            'evidence_type' => 'social_page',
            'evidence_path' => $account->profile_url,
            'notes' => "Self-reported: {$data['platform']} @{$data['handle']}, ".
                ($data['follower_count'] ?? 'unknown').' followers.',
        ]);
    }
}
