<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Actions\PurchaseFeaturedPlacement;
use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\FeaturedPlacement;
use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFeaturedPlacementTest extends TestCase
{
    use RefreshDatabase;

    private function paymentData(): array
    {
        return [
            'amount_ugx' => 50_000,
            'channel' => 'manual',
            'gateway' => 'manual',
            'gateway_ref' => fake()->unique()->uuid(),
        ];
    }

    public function test_happy_path_creates_placement_and_payment_and_updates_provider_cache(): void
    {
        $staff = User::factory()->create();
        $category = ServiceCategory::factory()->create();
        $district = District::factory()->create();

        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create(['entitlements' => ['featured_eligible' => true]]);
        $provider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        $placement = (new PurchaseFeaturedPlacement(new Entitlements))(
            $provider, $category, $district, 30, 50_000, $staff, $this->paymentData()
        );

        $this->assertSame($provider->id, $placement->provider_id);
        $this->assertDatabaseHas('billing_payments', [
            'subscription_id' => null,
            'status' => 'settled',
        ]);

        $provider->refresh();
        $this->assertEqualsWithDelta($placement->ends_at, $provider->featured_until, 1);
    }

    public function test_rejected_when_not_featured_eligible(): void
    {
        $staff = User::factory()->create();
        $provider = Provider::factory()->create();
        $plan = Plan::factory()->create(['entitlements' => ['featured_eligible' => false]]);
        $provider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        $this->expectException(\RuntimeException::class);

        (new PurchaseFeaturedPlacement(new Entitlements))(
            $provider, null, null, 30, 50_000, $staff, $this->paymentData()
        );
    }

    public function test_rejected_when_the_tuple_is_already_taken(): void
    {
        $staff = User::factory()->create();
        $category = ServiceCategory::factory()->create();
        $district = District::factory()->create();

        $plan = Plan::factory()->create(['entitlements' => ['featured_eligible' => true]]);

        $firstProvider = Provider::factory()->create();
        $firstProvider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        (new PurchaseFeaturedPlacement(new Entitlements))(
            $firstProvider, $category, $district, 30, 50_000, $staff, $this->paymentData()
        );

        $secondProvider = Provider::factory()->create();
        $secondProvider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        $this->expectException(\RuntimeException::class);

        (new PurchaseFeaturedPlacement(new Entitlements))(
            $secondProvider, $category, $district, 30, 50_000, $staff, $this->paymentData()
        );
    }

    public function test_allowed_once_the_prior_placements_window_has_passed(): void
    {
        $staff = User::factory()->create();
        $category = ServiceCategory::factory()->create();
        $district = District::factory()->create();
        $plan = Plan::factory()->create(['entitlements' => ['featured_eligible' => true]]);

        $firstProvider = Provider::factory()->create();
        $firstProvider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        FeaturedPlacement::factory()->create([
            'provider_id' => $firstProvider->id,
            'service_category_id' => $category->id,
            'district_id' => $district->id,
            'starts_at' => now()->subDays(60),
            'ends_at' => now()->subDays(1),
        ]);

        $secondProvider = Provider::factory()->create();
        $secondProvider->forceFill(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()])->save();

        $placement = (new PurchaseFeaturedPlacement(new Entitlements))(
            $secondProvider, $category, $district, 30, 50_000, $staff, $this->paymentData()
        );

        $this->assertSame($secondProvider->id, $placement->provider_id);
    }
}
