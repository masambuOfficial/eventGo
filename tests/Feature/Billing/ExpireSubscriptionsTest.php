<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpireSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expires_lapsed_subscriptions_and_resets_the_provider_plan_cache(): void
    {
        $freePlan = Plan::factory()->create(['code' => 'free']);
        $paidPlan = Plan::factory()->create(['code' => 'pro-30']);

        $provider = Provider::factory()->create(['plan_id' => $paidPlan->id, 'plan_expires_at' => now()->subDay()]);
        $subscription = Subscription::factory()->create([
            'subscriber_id' => $provider->id,
            'plan_id' => $paidPlan->id,
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);

        $stillActiveProvider = Provider::factory()->create(['plan_id' => $paidPlan->id, 'plan_expires_at' => now()->addMonth()]);
        $stillActiveSubscription = Subscription::factory()->create([
            'subscriber_id' => $stillActiveProvider->id,
            'plan_id' => $paidPlan->id,
            'status' => 'active',
            'expires_at' => now()->addMonth(),
        ]);

        Artisan::call('subscriptions:expire');

        $this->assertSame('expired', $subscription->fresh()->status);
        $provider->refresh();
        $this->assertSame($freePlan->id, $provider->plan_id);
        $this->assertNull($provider->plan_expires_at);

        $this->assertSame('active', $stillActiveSubscription->fresh()->status);
        $this->assertSame($paidPlan->id, $stillActiveProvider->fresh()->plan_id);
    }
}
