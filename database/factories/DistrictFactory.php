<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'region' => fake()->randomElement(['central', 'eastern', 'northern', 'western']),
            'is_active' => true,
        ];
    }
}
