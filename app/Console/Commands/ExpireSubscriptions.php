<?php

namespace App\Console\Commands;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §8.2: "graceful degradation, never deletion" — on expiry the
 * provider drops to free-tier limits, their profile/portfolio/reviews stay.
 */
class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Move active subscriptions past their expiry to expired, and reset the provider plan cache to free';

    public function handle(): int
    {
        $freePlanId = Plan::where('code', 'free')->value('id');

        Subscription::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->each(function (Subscription $subscription) use ($freePlanId) {
                try {
                    DB::transaction(function () use ($subscription, $freePlanId) {
                        $subscription->update(['status' => 'expired']);

                        $provider = Provider::whereKey($subscription->subscriber_id)->lockForUpdate()->first();

                        if ($provider && $provider->plan_id === $subscription->plan_id) {
                            $provider->forceFill([
                                'plan_id' => $freePlanId,
                                'plan_expires_at' => null,
                            ])->save();
                        }
                    });
                } catch (\Throwable $e) {
                    Log::warning('Could not expire subscription.', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
