<?php

namespace App\Domain\Reporting\Queries;

use App\Domain\Billing\Models\BillingPayment;
use App\Domain\Billing\Models\Subscription;

/**
 * Gated behind a minimum-data floor: subscriptions/billing_payments are
 * only populated by the manual staff-activation flow so far, meaning real
 * counts are likely near-zero. "ARPU: UGX 0" across a whole section is more
 * misleading than one honest placeholder saying there isn't enough
 * activity yet.
 */
class RevenueMetrics
{
    private const MINIMUM_SUBSCRIPTIONS = 5;

    public function hasEnoughData(): bool
    {
        return Subscription::count() >= self::MINIMUM_SUBSCRIPTIONS;
    }

    public function __invoke(): array
    {
        if (! $this->hasEnoughData()) {
            return [];
        }

        $totalSubscriptions = Subscription::count();
        $activeSubscriptions = Subscription::where('status', 'active')->where('expires_at', '>', now())->count();

        return [
            'total_subscriptions_sold' => $totalSubscriptions,
            'currently_active_subscriptions' => $activeSubscriptions,
            'total_revenue_ugx' => (int) BillingPayment::where('status', 'settled')->sum('amount_ugx'),
            'paying_providers' => Subscription::where('subscriber_type', 'provider')->distinct('subscriber_id')->count('subscriber_id'),
        ];
    }
}
