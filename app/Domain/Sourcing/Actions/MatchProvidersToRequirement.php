<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\FeaturedPlacement;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Architecture §9.6: category + district fit, verification tier,
 * Bayesian-shrunk rating, responsiveness, recency, weighted, then
 * multiplied by two capped paid terms — `plan_boost` (from the provider's
 * plan entitlements) and `featured_multiplier` (only when an active
 * FeaturedPlacement matches this requirement's category/district). Both
 * caps live in `config/ranking.php` with written rationale, per
 * architecture's own instruction.
 *
 * Scored in PHP rather than one large SQL expression — candidate counts
 * at pilot scale (hundreds of providers) make this the boring choice.
 */
class MatchProvidersToRequirement
{
    private const BAYESIAN_MIN_VOTES = 5;

    private const BAYESIAN_MEAN_RATING = 3.5;

    public function __construct(private Entitlements $entitlements)
    {
    }

    /**
     * @return Collection<int, array{provider: Provider, score: float, is_featured: bool}>
     */
    public function __invoke(Requirement $requirement): Collection
    {
        $requirement->loadMissing('event', 'category');

        $districtId = $requirement->event->district_id;

        $providers = Provider::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->where('service_category_id', $requirement->service_category_id))
            ->when($districtId, fn ($query) => $query->whereHas(
                'serviceAreas',
                fn ($areas) => $areas->where('districts.id', $districtId)
            ))
            ->with(['services' => fn ($query) => $query->where('service_category_id', $requirement->service_category_id)])
            ->get();

        return $providers
            ->map(function (Provider $provider) use ($requirement, $districtId) {
                $isFeatured = $this->isFeaturedFor($provider, $requirement, $districtId);

                return [
                    'provider' => $provider,
                    'score' => $this->score($provider, $requirement, $isFeatured),
                    'is_featured' => $isFeatured,
                ];
            })
            ->sortByDesc('score')
            ->values();
    }

    private function score(Provider $provider, Requirement $requirement, bool $isFeatured): float
    {
        $core =
            0.30 * $this->categoryAndCapacityFit($provider, $requirement)
            + 0.20 * ($provider->verification_tier / 3)
            + 0.20 * $this->bayesianRating($provider)
            + 0.20 * $this->responsiveness($provider)
            + 0.10 * $this->recencyOfActivity($provider);

        $planBoost = min(
            $this->entitlements->for($provider)['search_boost'] ?? 1.0,
            config('ranking.plan_boost_cap')
        );

        $featuredMultiplier = $isFeatured ? config('ranking.featured_multiplier') : 1.0;

        return $core * $planBoost * $featuredMultiplier;
    }

    /**
     * Cheap pre-check against the cached `featured_until` timestamp before
     * bothering with the tuple-match query — most providers won't have it
     * set at all.
     */
    private function isFeaturedFor(Provider $provider, Requirement $requirement, ?int $districtId): bool
    {
        if (! $this->entitlements->isFeatured($provider)) {
            return false;
        }

        return FeaturedPlacement::query()
            ->where('provider_id', $provider->id)
            ->coveringRequirement($requirement->service_category_id, $districtId)
            ->active()
            ->exists();
    }

    private function categoryAndCapacityFit(Provider $provider, Requirement $requirement): float
    {
        $category = $requirement->category;

        if (! $category || ! $category->requires_capacity) {
            return 1.0;
        }

        $guestCount = $requirement->event->guest_count_expected;

        if ($guestCount === null) {
            return 1.0;
        }

        $service = $provider->services->first();

        if (! $service) {
            return 0.5;
        }

        $withinMin = $service->min_capacity === null || $guestCount >= $service->min_capacity;
        $withinMax = $service->max_capacity === null || $guestCount <= $service->max_capacity;

        return $withinMin && $withinMax ? 1.0 : 0.5;
    }

    private function bayesianRating(Provider $provider): float
    {
        $votes = $provider->rating_count ?? 0;
        $rating = $provider->rating_avg !== null ? (float) $provider->rating_avg : self::BAYESIAN_MEAN_RATING;

        $shrunk = ($votes / ($votes + self::BAYESIAN_MIN_VOTES)) * $rating
            + (self::BAYESIAN_MIN_VOTES / ($votes + self::BAYESIAN_MIN_VOTES)) * self::BAYESIAN_MEAN_RATING;

        return $shrunk / 5;
    }

    private function responsiveness(Provider $provider): float
    {
        $rate = $provider->response_rate !== null ? ((float) $provider->response_rate) / 100 : 0.5;

        $median = $provider->median_response_minutes;
        $speed = $median === null ? 0.5 : max(0.0, min(1.0, 1 - ($median / (24 * 60))));

        return (0.6 * $rate) + (0.4 * $speed);
    }

    private function recencyOfActivity(Provider $provider): float
    {
        if (! $provider->updated_at) {
            return 0.5;
        }

        $daysSinceUpdate = $provider->updated_at->diffInDays(now());

        return max(0.0, min(1.0, 1 - ($daysSinceUpdate / 90)));
    }
}
