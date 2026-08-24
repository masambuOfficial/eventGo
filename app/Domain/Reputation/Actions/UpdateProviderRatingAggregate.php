<?php

namespace App\Domain\Reputation\Actions;

use App\Domain\Providers\Models\Provider;
use App\Domain\Reputation\Models\Review;

/**
 * Keeps `providers.rating_avg`/`rating_count` in sync with published
 * organiser_to_provider reviews. The Bayesian-shrunk ranking formula
 * (architecture §9.6) is a read-time concern for Sourcing/search — this
 * just maintains the raw stored aggregate it's computed from.
 */
class UpdateProviderRatingAggregate
{
    public function __invoke(Provider $provider): void
    {
        $stats = Review::query()
            ->join('bookings', 'bookings.id', '=', 'reviews.booking_id')
            ->where('bookings.provider_id', $provider->id)
            ->where('reviews.direction', 'organiser_to_provider')
            ->where('reviews.is_published', true)
            ->selectRaw('avg(reviews.rating) as avg_rating, count(*) as review_count')
            ->first();

        $provider->forceFill([
            'rating_avg' => $stats->review_count > 0 ? round($stats->avg_rating, 2) : null,
            'rating_count' => $stats->review_count,
        ])->save();
    }
}
