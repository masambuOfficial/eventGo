<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use Illuminate\Support\Collection;

/**
 * Architecture §9.6, simplified for the pilot: category + district fit,
 * verification tier, Bayesian-shrunk rating, responsiveness, recency. No
 * `plan_boost` or `featured_multiplier` — those read from the Billing
 * domain (Phase 5), which doesn't exist yet. Add them as extra multipliers
 * on top of this score later; the weighted core below shouldn't need to
 * change shape when that happens.
 *
 * Scored in PHP rather than one large SQL expression — candidate counts
 * at pilot scale (hundreds of providers) make this the boring choice.
 */
class MatchProvidersToRequirement
{
    private const BAYESIAN_MIN_VOTES = 5;

    private const BAYESIAN_MEAN_RATING = 3.5;

    /**
     * @return Collection<int, array{provider: Provider, score: float}>
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
            ->map(fn (Provider $provider) => [
                'provider' => $provider,
                'score' => $this->score($provider, $requirement),
            ])
            ->sortByDesc('score')
            ->values();
    }

    private function score(Provider $provider, Requirement $requirement): float
    {
        return
            0.30 * $this->categoryAndCapacityFit($provider, $requirement)
            + 0.20 * ($provider->verification_tier / 3)
            + 0.20 * $this->bayesianRating($provider)
            + 0.20 * $this->responsiveness($provider)
            + 0.10 * $this->recencyOfActivity($provider);
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
