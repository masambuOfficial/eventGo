<?php

namespace Database\Factories;

use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return [
            'requirement_id' => Requirement::factory(),
            'provider_id' => Provider::factory(),
            'submitted_by_user_id' => User::factory(),
            'total_ugx' => fake()->numberBetween(500_000, 20_000_000),
            'status' => 'draft',
        ];
    }
}
