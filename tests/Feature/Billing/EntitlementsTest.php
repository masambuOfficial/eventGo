<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\Plan;
use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitlementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_plan_resolves_correctly(): void
    {
        $plan = Plan::factory()->create(['entitlements' => ['search_boost' => 1.2]]);
        $provider = Provider::factory()->create(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()]);

        $entitlements = (new Entitlements)->for($provider);

        $this->assertSame(1.2, $entitlements['search_boost']);
    }

    public function test_expired_plan_falls_back_to_free_regardless_of_stale_plan_id(): void
    {
        $paidPlan = Plan::factory()->create(['entitlements' => ['search_boost' => 1.2]]);
        $freePlan = Plan::factory()->create(['code' => 'free', 'entitlements' => ['search_boost' => 1.0]]);
        $provider = Provider::factory()->create(['plan_id' => $paidPlan->id, 'plan_expires_at' => now()->subDay()]);

        $entitlements = (new Entitlements)->for($provider);

        $this->assertEquals(1.0, $entitlements['search_boost']);
    }

    public function test_never_subscribed_provider_resolves_free(): void
    {
        Plan::factory()->create(['code' => 'free', 'entitlements' => ['search_boost' => 1.0]]);
        $provider = Provider::factory()->create(['plan_id' => null, 'plan_expires_at' => null]);

        $entitlements = (new Entitlements)->for($provider);

        $this->assertEquals(1.0, $entitlements['search_boost']);
    }

    public function test_missing_free_plan_degrades_to_empty_array_rather_than_throwing(): void
    {
        $provider = Provider::factory()->create(['plan_id' => null, 'plan_expires_at' => null]);

        $entitlements = (new Entitlements)->for($provider);

        $this->assertSame([], $entitlements);
    }
}
