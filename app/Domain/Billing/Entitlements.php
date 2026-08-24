<?php

namespace App\Domain\Billing;

use App\Domain\Billing\Models\Plan;
use App\Domain\Providers\Models\Provider;

/**
 * Self-healing against a cache the nightly `subscriptions:expire` /
 * `featured-placements:expire` commands might not have refreshed yet —
 * same shape as Phase 4's `ForceCompleteBooking`. `providers.plan_id` is
 * only trusted while `plan_expires_at` is still in the future; otherwise
 * this resolves the seeded `free` plan regardless of what `plan_id` says.
 *
 * Returns an empty array — never throws — if even the free plan is
 * missing. Every reader treats a missing key as "no restriction, no boost"
 * (`?? null` for limits, `?? 1.0` for multipliers), so a reference-data gap
 * degrades to today's unrestricted behavior rather than fatal-erroring
 * every offer submission and ranking pass platform-wide.
 */
class Entitlements
{
    public function for(Provider $provider): array
    {
        if ($provider->plan_id !== null && $provider->plan_expires_at?->isFuture()) {
            $plan = Plan::find($provider->plan_id);

            if ($plan) {
                return $plan->entitlements;
            }
        }

        return Plan::where('code', 'free')->first()?->entitlements ?? [];
    }

    public function isFeatured(Provider $provider): bool
    {
        return $provider->featured_until !== null && $provider->featured_until->isFuture();
    }
}
