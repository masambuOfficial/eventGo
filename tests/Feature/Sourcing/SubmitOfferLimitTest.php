<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Billing\Entitlements;
use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\SubmitOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmitOfferLimitTest extends TestCase
{
    use RefreshDatabase;

    private function requirement(): Requirement
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);

        return Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => ServiceCategory::factory(),
            'status' => 'sourcing',
        ]);
    }

    private function offerData(): array
    {
        return [
            'total_ugx' => 1_000_000,
            'items' => [['description' => 'A', 'quantity' => 1, 'unit_price_ugx' => 1_000_000]],
        ];
    }

    public function test_free_tier_provider_is_blocked_at_the_sixth_offer_in_a_calendar_month(): void
    {
        Plan::factory()->create(['code' => 'free', 'entitlements' => ['max_offers_per_month' => 5]]);
        $provider = Provider::factory()->create();
        $submitOffer = new SubmitOffer(new Entitlements);

        for ($i = 0; $i < 5; $i++) {
            $submitOffer($this->requirement(), $provider, $provider->owner, $this->offerData());
        }

        $this->expectException(\RuntimeException::class);

        $submitOffer($this->requirement(), $provider, $provider->owner, $this->offerData());
    }

    public function test_unlimited_plan_provider_is_unaffected(): void
    {
        $plan = Plan::factory()->create(['entitlements' => ['max_offers_per_month' => null]]);
        $provider = Provider::factory()->create(['plan_id' => $plan->id, 'plan_expires_at' => now()->addMonth()]);
        $submitOffer = new SubmitOffer(new Entitlements);

        for ($i = 0; $i < 6; $i++) {
            $offer = $submitOffer($this->requirement(), $provider, $provider->owner, $this->offerData());
        }

        $this->assertNotNull($offer);
    }

    public function test_editing_an_already_submitted_offer_does_not_count_against_the_limit(): void
    {
        Plan::factory()->create(['code' => 'free', 'entitlements' => ['max_offers_per_month' => 1]]);
        $provider = Provider::factory()->create();
        $submitOffer = new SubmitOffer(new Entitlements);

        $requirement = $this->requirement();
        $offer = $submitOffer($requirement, $provider, $provider->owner, $this->offerData());

        // Editing the same (requirement, provider) offer again should not
        // re-count toward the monthly limit — $wasDraft guards this.
        $edited = $submitOffer($requirement, $provider, $provider->owner, [
            'total_ugx' => 2_000_000,
            'items' => [['description' => 'B', 'quantity' => 1, 'unit_price_ugx' => 2_000_000]],
        ]);

        $this->assertSame($offer->id, $edited->id);
        $this->assertSame(2_000_000, $edited->total_ugx);
    }
}
