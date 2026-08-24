<?php

namespace App\Domain\Reporting\Queries;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;

/**
 * Raw stage counts, deliberately not percentages — at pilot-scale volume a
 * funnel percentage swings wildly per single user and reads as fake
 * precision. Architecture §15's organiser/provider funnel stages, counted
 * rather than converted, until there's enough volume for a rate to mean
 * something.
 */
class FunnelCounts
{
    public function organiser(): array
    {
        $sourcingStatuses = ['sourcing', 'offers_received', 'shortlisted', 'awarded', 'booked', 'fulfilled'];
        $bookedStatuses = ['awarded', 'booked', 'fulfilled'];

        return [
            'events_created' => Event::count(),
            'requirements_committed' => Event::whereHas('requirements', fn ($q) => $q->where('status', '!=', 'draft'))->count(),
            'sourcing_started' => Event::whereHas('requirements', fn ($q) => $q->whereIn('status', $sourcingStatuses))->count(),
            'offer_accepted' => Event::whereHas('requirements', fn ($q) => $q->whereIn('status', $bookedStatuses))->count(),
            'booking_confirmed' => Booking::distinct('event_id')->count('event_id'),
        ];
    }

    public function provider(): array
    {
        $offeredProviderIds = Offer::whereNotNull('submitted_at')->distinct()->pluck('provider_id');

        return [
            'registered' => Provider::count(),
            'email_verified' => Provider::whereHas('owner', fn ($q) => $q->whereNotNull('email_verified_at'))->count(),
            'profile_60_percent' => Provider::where('profile_completeness', '>=', 60)->count(),
            'tier_1_verified' => Provider::where('verification_tier', '>=', 1)->count(),
            'first_offer_submitted' => Provider::whereIn('id', $offeredProviderIds)->count(),
        ];
    }
}
