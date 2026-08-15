<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Actions\MatchProvidersToRequirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchProvidersToRequirementTest extends TestCase
{
    use RefreshDatabase;

    private function requirementIn(District $district, ServiceCategory $category): Requirement
    {
        $event = Event::factory()->create([
            'event_type_id' => EventType::factory(),
            'district_id' => $district->id,
            'guest_count_expected' => 300,
        ]);

        return Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => $category->id,
        ]);
    }

    public function test_it_excludes_providers_outside_the_category_or_district(): void
    {
        $kampala = District::factory()->create();
        $mbarara = District::factory()->create();
        $catering = ServiceCategory::factory()->create();
        $photography = ServiceCategory::factory()->create();

        $requirement = $this->requirementIn($kampala, $catering);

        $inCategoryAndDistrict = Provider::factory()->create();
        $inCategoryAndDistrict->services()->create(['service_category_id' => $catering->id]);
        $inCategoryAndDistrict->serviceAreas()->attach($kampala->id);

        $wrongDistrict = Provider::factory()->create();
        $wrongDistrict->services()->create(['service_category_id' => $catering->id]);
        $wrongDistrict->serviceAreas()->attach($mbarara->id);

        $wrongCategory = Provider::factory()->create();
        $wrongCategory->services()->create(['service_category_id' => $photography->id]);
        $wrongCategory->serviceAreas()->attach($kampala->id);

        $matches = (new MatchProvidersToRequirement)($requirement);

        $this->assertCount(1, $matches);
        $this->assertTrue($matches->first()['provider']->is($inCategoryAndDistrict));
    }

    public function test_it_ranks_higher_tier_and_rated_providers_first(): void
    {
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create();
        $requirement = $this->requirementIn($district, $category);

        $strong = Provider::factory()->create([
            'verification_tier' => 3,
            'rating_avg' => 4.9,
            'rating_count' => 50,
            'response_rate' => 95,
            'median_response_minutes' => 20,
        ]);
        $strong->services()->create(['service_category_id' => $category->id]);
        $strong->serviceAreas()->attach($district->id);

        $weak = Provider::factory()->create([
            'verification_tier' => 0,
            'rating_avg' => null,
            'rating_count' => 0,
            'response_rate' => null,
            'median_response_minutes' => null,
        ]);
        $weak->services()->create(['service_category_id' => $category->id]);
        $weak->serviceAreas()->attach($district->id);

        $matches = (new MatchProvidersToRequirement)($requirement);

        $this->assertCount(2, $matches);
        $this->assertTrue($matches->first()['provider']->is($strong));
        $this->assertGreaterThan($matches->last()['score'], $matches->first()['score']);
    }

    public function test_capacity_mismatch_lowers_score_but_does_not_exclude(): void
    {
        $district = District::factory()->create();
        $category = ServiceCategory::factory()->create(['requires_capacity' => true]);
        $requirement = $this->requirementIn($district, $category);

        $fits = Provider::factory()->create();
        $fits->services()->create([
            'service_category_id' => $category->id,
            'min_capacity' => 100,
            'max_capacity' => 500,
        ]);
        $fits->serviceAreas()->attach($district->id);

        $tooSmall = Provider::factory()->create();
        $tooSmall->services()->create([
            'service_category_id' => $category->id,
            'min_capacity' => 10,
            'max_capacity' => 50,
        ]);
        $tooSmall->serviceAreas()->attach($district->id);

        $matches = (new MatchProvidersToRequirement)($requirement)->keyBy(fn ($row) => $row['provider']->id);

        $this->assertGreaterThan(
            $matches[$tooSmall->id]['score'],
            $matches[$fits->id]['score'],
        );
    }
}
