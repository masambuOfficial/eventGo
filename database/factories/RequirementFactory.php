<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Requirement>
 */
class RequirementFactory extends Factory
{
    protected $model = Requirement::class;

    public function definition(): array
    {
        return [
            'public_id' => (string) Str::ulid(),
            'event_id' => Event::factory(),
            'service_category_id' => ServiceCategory::factory(),
            'title' => fake()->words(3, true),
            'quantity' => 1,
            'budget_estimate_ugx' => fake()->numberBetween(500_000, 20_000_000),
            'priority' => 'important',
            'status' => 'draft',
            'source' => 'manual',
        ];
    }
}
