<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingAmendment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingAmendment>
 */
class BookingAmendmentFactory extends Factory
{
    protected $model = BookingAmendment::class;

    public function definition(): array
    {
        $previousTotal = fake()->numberBetween(500_000, 20_000_000);

        return [
            'booking_id' => Booking::factory(),
            'changed_by_user_id' => User::factory(),
            'previous_total_ugx' => $previousTotal,
            'new_total_ugx' => $previousTotal + fake()->numberBetween(-100_000, 1_000_000),
            'note' => fake()->sentence(),
        ];
    }
}
