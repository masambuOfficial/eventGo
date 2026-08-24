<?php

namespace Database\Factories;

use App\Domain\Billing\Models\FeaturedPlacement;
use App\Domain\Providers\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeaturedPlacement>
 */
class FeaturedPlacementFactory extends Factory
{
    protected $model = FeaturedPlacement::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'price_ugx' => fake()->numberBetween(20_000, 100_000),
        ];
    }
}
