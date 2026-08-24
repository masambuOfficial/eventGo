<?php

namespace App\Console\Commands;

use App\Domain\Billing\Models\Subscription;
use App\Domain\Notifications\Actions\NotifyUser;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §8.2: renewal prompts at T-14/T-7/T-1/expiry. In-app only
 * this phase — matches Phase 4's precedent, no SMS/email infra exists yet
 * even though architecture flags SMS as worth the cost specifically here.
 *
 * Idempotency reuses `notification_deliveries.idempotency_key`'s existing
 * unique index rather than a new dedupe table — a deterministic key per
 * (subscription, threshold) means running this command twice in the same
 * day is a no-op on the second pass, caught below and treated as expected
 * steady state, not an error. Same shape as `OpenBookingThread` trusting
 * `uq_thread_subject` instead of pre-checking.
 */
class SendRenewalPrompts extends Command
{
    private const THRESHOLDS = [14, 7, 1, 0];

    protected $signature = 'subscriptions:renewal-prompts';

    protected $description = 'Notify providers whose subscription is approaching or has reached expiry';

    public function handle(NotifyUser $notifyUser): int
    {
        foreach (self::THRESHOLDS as $threshold) {
            $targetDate = now()->addDays($threshold)->toDateString();

            Subscription::where('status', 'active')
                ->whereDate('expires_at', $targetDate)
                ->with('plan', 'provider.owner')
                ->each(function (Subscription $subscription) use ($notifyUser, $threshold) {
                    $owner = $subscription->provider?->owner;

                    if (! $owner) {
                        return;
                    }

                    try {
                        $notifyUser(
                            $owner,
                            'subscription_renewal',
                            [
                                'subscription_id' => $subscription->id,
                                'plan_name' => $subscription->plan->name,
                                'expires_at' => $subscription->expires_at->toDateString(),
                                'threshold_days' => $threshold,
                            ],
                            "subscription_renewal:{$subscription->id}:t-{$threshold}"
                        );
                    } catch (QueryException $e) {
                        // Already sent this threshold — expected steady
                        // state when the command runs more than once a day.
                    } catch (\Throwable $e) {
                        Log::warning('Could not send renewal prompt.', [
                            'subscription_id' => $subscription->id,
                            'threshold' => $threshold,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });
        }

        return self::SUCCESS;
    }
}
