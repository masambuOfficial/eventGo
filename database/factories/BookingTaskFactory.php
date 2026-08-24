<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingTask>
 */
class BookingTaskFactory extends Factory
{
    protected $model = BookingTask::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'title' => fake()->sentence(4),
            'owner_side' => fake()->randomElement(['organiser', 'provider', 'both']),
            'status' => 'open',
            'sort_order' => 0,
        ];
    }
}
