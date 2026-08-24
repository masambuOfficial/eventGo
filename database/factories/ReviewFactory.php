<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Reputation\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'direction' => 'organiser_to_provider',
            'author_user_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
            'is_published' => true,
            'published_at' => now(),
        ];
    }
}
