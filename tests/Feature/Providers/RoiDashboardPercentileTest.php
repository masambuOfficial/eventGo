<?php

namespace Tests\Feature\Providers;

use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Narrow test for the peer-group/percentile query specifically — easy to
 * get subtly wrong (off-by-one in PERCENT_RANK interpretation, an
 * incorrect peer-group join) and it's a number shown to a paying customer.
 * Not a full page-render test — matches this codebase's habit of not
 * testing every read-only view beyond what's needed.
 */
class RoiDashboardPercentileTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_top_percentile_among_same_top_level_category_peers(): void
    {
        $topCategory = ServiceCategory::factory()->create(['parent_id' => null]);
        $subCategory = ServiceCategory::factory()->create(['parent_id' => $topCategory->id]);
        $unrelatedCategory = ServiceCategory::factory()->create(['parent_id' => null]);

        $me = Provider::factory()->create(['response_rate' => 90, 'median_response_minutes' => 20, 'is_active' => true]);
        $me->services()->create(['service_category_id' => $subCategory->id]);

        // Two weaker peers under the same top-level category.
        foreach ([40, 60] as $rate) {
            $peer = Provider::factory()->create(['response_rate' => $rate, 'is_active' => true]);
            $peer->services()->create(['service_category_id' => $subCategory->id]);
        }

        // A provider in an unrelated category — must not affect the percentile.
        $unrelated = Provider::factory()->create(['response_rate' => 1, 'is_active' => true]);
        $unrelated->services()->create(['service_category_id' => $unrelatedCategory->id]);

        $owner = $me->owner;

        $response = $this->actingAs($owner)->get(route('provider.roi.index'));

        $response->assertOk();
        // Best of 3 peers -> PERCENT_RANK 1.0 -> top 0% (i.e. the top spot).
        $response->assertSee('top 0%');
        $response->assertSee($topCategory->name);
    }

    public function test_shows_no_benchmark_when_response_rate_is_not_yet_computed(): void
    {
        $provider = Provider::factory()->create(['response_rate' => null]);

        $response = $this->actingAs($provider->owner)->get(route('provider.roi.index'));

        $response->assertOk();
        $response->assertSee('Not enough activity yet');
    }
}
