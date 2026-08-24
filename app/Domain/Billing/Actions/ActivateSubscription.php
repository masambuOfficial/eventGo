<?php

namespace App\Domain\Billing\Actions;

use App\Domain\Billing\Models\BillingPayment;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The manual activation path architecture §8.2 requires regardless of
 * whether a real PSP is ever integrated: staff enters a mobile money
 * reference the customer read over the phone. `BillingPayment`'s insert is
 * what actually throws on a duplicate [gateway, gateway_ref] — relies on
 * `uq_billing_gateway_ref` as the idempotency backstop, the same way
 * `OpenBookingThread` relies on `uq_thread_subject` rather than
 * pre-checking. The caller (the admin Livewire component) must catch that.
 */
class ActivateSubscription
{
    public function __invoke(Provider $provider, Plan $plan, User $activatedBy, array $paymentData): Subscription
    {
        return DB::transaction(function () use ($provider, $plan, $paymentData) {
            $lockedProvider = Provider::whereKey($provider->id)->lockForUpdate()->first();

            $existing = Subscription::where('subscriber_type', 'provider')
                ->where('subscriber_id', $lockedProvider->id)
                ->active()
                ->first();

            // Extend from the later of now() or the existing expiry, never
            // blindly from the existing expiry — a provider reactivating
            // after a lapse must not be under-credited by days already lost.
            $extendFrom = $existing && $existing->expires_at->isFuture() ? $existing->expires_at : now();
            $expiresAt = $extendFrom->copy()->addDays($plan->duration_days);

            $subscription = Subscription::create([
                'subscriber_type' => 'provider',
                'subscriber_id' => $lockedProvider->id,
                'plan_id' => $plan->id,
                'starts_at' => now(),
                'expires_at' => $expiresAt,
                'status' => 'active',
            ]);

            BillingPayment::create([
                'subscription_id' => $subscription->id,
                'amount_ugx' => $paymentData['amount_ugx'],
                'channel' => $paymentData['channel'],
                'gateway' => $paymentData['gateway'],
                'gateway_ref' => $paymentData['gateway_ref'],
                'payer_msisdn' => $paymentData['payer_msisdn'] ?? null,
                'payer_name' => $paymentData['payer_name'] ?? null,
                'status' => 'settled',
                'paid_at' => now(),
            ]);

            $lockedProvider->forceFill([
                'plan_id' => $plan->id,
                'plan_expires_at' => $expiresAt,
            ])->save();

            return $subscription;
        });
    }
}
