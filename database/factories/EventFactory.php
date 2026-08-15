<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Events\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = fake()->words(3, true).' event';

        return [
            'public_id' => (string) Str::ulid(),
            'slug' => Event::generateSlug($name),
            'owner_user_id' => User::factory(),
            'name' => $name,
            'event_type_id' => EventType::factory(),
            'starts_at' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'guest_count_expected' => fake()->numberBetween(50, 800),
            'status' => 'draft',
        ];
    }
}
