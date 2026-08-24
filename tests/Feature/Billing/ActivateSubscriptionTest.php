<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Actions\ActivateSubscription;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function paymentData(): array
    {
        return [
            'amount_ugx' => 150_000,
            'channel' => 'manual',
            'gateway' => 'manual',
            'gateway_ref' => fake()->unique()->uuid(),
            'payer_msisdn' => '256772000000',
            'payer_name' => 'Test Payer',
        ];
    }

    public function test_fresh_activation_creates_subscription_and_payment_and_updates_provider_cache(): void
    {
        $staff = User::factory()->create();
        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create(['duration_days' => 90]);

        $subscription = (new ActivateSubscription)($provider, $plan, $staff, $this->paymentData());

        $this->assertSame('active', $subscription->status);
        $this->assertSame($plan->id, $subscription->plan_id);
        $this->assertEqualsWithDelta(now()->addDays(90), $subscription->expires_at, 5);

        $this->assertDatabaseHas('billing_payments', [
            'subscription_id' => $subscription->id,
            'status' => 'settled',
        ]);

        $provider->refresh();
        $this->assertSame($plan->id, $provider->plan_id);
        $this->assertEqualsWithDelta($subscription->expires_at, $provider->plan_expires_at, 1);
    }

    public function test_renewal_while_still_active_extends_from_existing_expiry(): void
    {
        $staff = User::factory()->create();
        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create(['duration_days' => 30]);

        $activate = new ActivateSubscription;
        $first = $activate($provider, $plan, $staff, $this->paymentData());

        $second = $activate($provider->fresh(), $plan, $staff, $this->paymentData());

        $this->assertEqualsWithDelta($first->expires_at->copy()->addDays(30), $second->expires_at, 5);
    }

    public function test_renewal_after_lapse_extends_from_now_not_the_stale_past_date(): void
    {
        $staff = User::factory()->create();
        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create(['duration_days' => 30]);

        // Simulate a lapsed subscription: expired weeks ago.
        Subscription::factory()->create([
            'subscriber_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'expired',
            'starts_at' => now()->subDays(60),
            'expires_at' => now()->subDays(30),
        ]);

        $subscription = (new ActivateSubscription)($provider, $plan, $staff, $this->paymentData());

        $this->assertEqualsWithDelta(now()->addDays(30), $subscription->expires_at, 5);
    }

    public function test_duplicate_gateway_ref_fails_cleanly(): void
    {
        $staff = User::factory()->create();
        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create();
        $paymentData = $this->paymentData();

        $activate = new ActivateSubscription;
        $activate($provider, $plan, $staff, $paymentData);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $activate($provider->fresh(), $plan, $staff, $paymentData);
    }
}
