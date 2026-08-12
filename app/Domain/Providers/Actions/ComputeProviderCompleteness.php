<?php

namespace App\Domain\Providers\Actions;

use App\Domain\Providers\Models\Provider;

class ComputeProviderCompleteness
{
    public function __invoke(Provider $provider): int
    {
        $score = 10; // business_name + slug, always present once a provider row exists

        if (filled($provider->about)) {
            $score += 15;
        }

        if (filled($provider->primary_phone_e164)) {
            $score += 10;
        }

        if (filled($provider->base_district_id)) {
            $score += 10;
        }

        if ($provider->services()->exists()) {
            $score += 20;
        }

        if ($provider->serviceAreas()->exists()) {
            $score += 15;
        }

        if ($provider->media()->exists()) {
            $score += 15;
        }

        if ($provider->socialAccounts()->exists()) {
            $score += 5;
        }

        $provider->forceFill(['profile_completeness' => $score])->save();

        return $score;
    }
}
