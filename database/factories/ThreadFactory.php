<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Messaging\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        return [
            'subject_type' => 'booking',
            'subject_id' => Booking::factory(),
        ];
    }
}
