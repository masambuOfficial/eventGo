<?php

namespace Database\Factories;

use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'owner_user_id' => User::factory(),
            'business_name' => $name,
            'normalised_name' => Str::lower(Str::slug($name, ' ')),
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
