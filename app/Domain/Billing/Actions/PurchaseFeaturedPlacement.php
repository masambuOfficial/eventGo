<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\BillingPayment;
use App\Domain\Billing\Models\FeaturedPlacement;
use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * A scoped placement and the null/null "everywhere" placement can both
 * match the same requirement and both get boosted/labelled "Sponsored"
 * simultaneously — an accepted pilot-scale gap, not a bug. The cap check
 * below is a genuine check-then-act race with no unique index to fall back
 * on (unlike gateway_ref/thread_subject elsewhere in this app), so the
 * lockForUpdate() here is load-bearing. Do not remove it.
 */
class PurchaseFeaturedPlacement
{
    public function __construct(private Entitlements $entitlements)
    {
    }

    public function __invoke(
        Provider $provider,
        ?ServiceCategory $category,
        ?District $district,
        int $durationDays,
        int $priceUgx,
        User $activatedBy,
        array $paymentData
    ): FeaturedPlacement {
        if (! ($this->entitlements->for($provider)['featured_eligible'] ?? false)) {
            throw new RuntimeException('This provider\'s plan does not include featured placement.');
        }

        return DB::transaction(function () use ($provider, $category, $district, $durationDays, $priceUgx, $paymentData) {
            $activeCount = FeaturedPlacement::query()
                ->matching($category?->id, $district?->id)
                ->active()
                ->lockForUpdate()
                ->count();

            if ($activeCount >= config('billing.featured_slots_per_tuple')) {
                throw new RuntimeException('This category and district combination is already fully sponsored.');
            }

            $startsAt = now();
            $endsAt = now()->addDays($durationDays);

            $placement = FeaturedPlacement::create([
                'provider_id' => $provider->id,
                'service_category_id' => $category?->id,
                'district_id' => $district?->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'price_ugx' => $priceUgx,
            ]);

            BillingPayment::create([
                'subscription_id' => null,
                'amount_ugx' => $paymentData['amount_ugx'],
                'channel' => $paymentData['channel'],
                'gateway' => $paymentData['gateway'],
                'gateway_ref' => $paymentData['gateway_ref'],
                'payer_msisdn' => $paymentData['payer_msisdn'] ?? null,
                'payer_name' => $paymentData['payer_name'] ?? null,
                'status' => 'settled',
                'paid_at' => now(),
            ]);

            $lockedProvider = Provider::whereKey($provider->id)->lockForUpdate()->first();
            $currentFeaturedUntil = $lockedProvider->featured_until;

            $lockedProvider->forceFill([
                'featured_until' => $currentFeaturedUntil && $currentFeaturedUntil->gt($endsAt) ? $currentFeaturedUntil : $endsAt,
            ])->save();

            return $placement;
        });
    }
}
