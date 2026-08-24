<?php

namespace Database\Factories;

use App\Domain\Billing\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'audience' => 'provider',
            'price_ugx' => fake()->numberBetween(50_000, 500_000),
            'duration_days' => 30,
            'entitlements' => ['max_offers_per_month' => null, 'analytics' => true, 'portfolio_slots' => 30, 'search_boost' => 1.2, 'featured_eligible' => true],
            'is_active' => true,
        ];
    }
}
