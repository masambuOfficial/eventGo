<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventType>
 */
class EventTypeFactory extends Factory
{
    protected $model = EventType::class;

    public function definition(): array
    {
        $name = fake()->unique()->word().' event';

        return [
            'slug' => Str::slug($name),
            'name' => ucfirst($name),
            'is_active' => true,
        ];
    }
}
