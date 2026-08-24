<?php

namespace App\Domain\Reporting\Queries;

use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\Opportunity;

/**
 * Architecture §15: "% of requirements published that receive at least one
 * offer within 48h — the single most important number." Shown with its row
 * count ("3 of 5"), never a bare percentage, so a handful of pilot-scale
 * rows doesn't read as more data than it is.
 */
class LiquidityMetrics
{
    use ComputesMedian;

    public function __invoke(): array
    {
        $opportunities = Opportunity::whereNotNull('published_at')->get(['requirement_id', 'published_at']);

        if ($opportunities->isEmpty()) {
            return [
                'published_count' => 0,
                'within_48h_count' => 0,
                'within_48h_percent' => null,
                'median_hours_to_first_offer' => null,
                'offers_per_requirement' => null,
            ];
        }

        $firstOfferAt = Offer::whereNotNull('submitted_at')
            ->whereIn('requirement_id', $opportunities->pluck('requirement_id'))
            ->orderBy('submitted_at')
            ->get(['requirement_id', 'submitted_at'])
            ->groupBy('requirement_id')
            ->map(fn ($offers) => $offers->first()->submitted_at);

        $offerCounts = Offer::whereIn('requirement_id', $opportunities->pluck('requirement_id'))
            ->selectRaw('requirement_id, count(*) as total')
            ->groupBy('requirement_id')
            ->pluck('total', 'requirement_id');

        $within48h = 0;
        $hoursToFirstOffer = [];

        foreach ($opportunities as $opportunity) {
            $firstOffer = $firstOfferAt->get($opportunity->requirement_id);

            if ($firstOffer) {
                $hours = $opportunity->published_at->diffInHours($firstOffer);
                $hoursToFirstOffer[] = $hours;

                if ($firstOffer->lte($opportunity->published_at->copy()->addHours(48))) {
                    $within48h++;
                }
            }
        }

        return [
            'published_count' => $opportunities->count(),
            'within_48h_count' => $within48h,
            'within_48h_percent' => round(($within48h / $opportunities->count()) * 100),
            'median_hours_to_first_offer' => $this->median($hoursToFirstOffer),
            'offers_per_requirement' => round($offerCounts->sum() / $opportunities->count(), 1),
        ];
    }

}
