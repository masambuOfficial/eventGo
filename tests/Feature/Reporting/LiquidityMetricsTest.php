<?php

namespace Tests\Feature\Reporting;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Reporting\Queries\LiquidityMetrics;
use App\Domain\Sourcing\Models\Offer;
use App\Domain\Sourcing\Models\Opportunity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiquidityMetricsTest extends TestCase
{
    use RefreshDatabase;

    private function requirement(): Requirement
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);

        return Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => ServiceCategory::factory(),
        ]);
    }

    public function test_computes_the_48h_liquidity_rate_and_median_time_to_first_offer(): void
    {
        // Requirement A: offer submitted within 48h.
        $requirementA = $this->requirement();
        Opportunity::create(['requirement_id' => $requirementA->id, 'published_at' => now()->subDays(5), 'status' => 'open']);
        Offer::factory()->create(['requirement_id' => $requirementA->id, 'submitted_at' => now()->subDays(5)->addHours(10)]);

        // Requirement B: offer submitted after 48h.
        $requirementB = $this->requirement();
        Opportunity::create(['requirement_id' => $requirementB->id, 'published_at' => now()->subDays(5), 'status' => 'open']);
        Offer::factory()->create(['requirement_id' => $requirementB->id, 'submitted_at' => now()->subDays(5)->addHours(80)]);

        $result = (new LiquidityMetrics)();

        $this->assertSame(2, $result['published_count']);
        $this->assertSame(1, $result['within_48h_count']);
        $this->assertSame(50.0, (float) $result['within_48h_percent']);
        // Median of [10h, 80h] is their average, not the smaller value.
        $this->assertSame(45.0, $result['median_hours_to_first_offer']);
    }

    public function test_returns_nulls_when_nothing_is_published(): void
    {
        $result = (new LiquidityMetrics)();

        $this->assertSame(0, $result['published_count']);
        $this->assertNull($result['within_48h_percent']);
    }

    public function test_a_requirement_with_no_offers_at_all_is_excluded_from_the_median_but_counted_as_published(): void
    {
        $requirement = $this->requirement();
        Opportunity::create(['requirement_id' => $requirement->id, 'published_at' => now()->subDays(3), 'status' => 'open']);

        $result = (new LiquidityMetrics)();

        $this->assertSame(1, $result['published_count']);
        $this->assertSame(0, $result['within_48h_count']);
    }
}
