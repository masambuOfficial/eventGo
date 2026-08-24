<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Bookings\Models\BookingFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingFile>
 */
class BookingFileFactory extends Factory
{
    protected $model = BookingFile::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'label' => fake()->words(3, true),
            'path' => 'booking-files/'.fake()->uuid().'.pdf',
            'mime' => 'application/pdf',
            'size_bytes' => fake()->numberBetween(10_000, 2_000_000),
        ];
    }
}
