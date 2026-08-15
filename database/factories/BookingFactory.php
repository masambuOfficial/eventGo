<?php

namespace Database\Factories;

use App\Domain\Bookings\Models\Booking;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use App\Domain\Sourcing\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $totalUgx = fake()->numberBetween(500_000, 20_000_000);

        return [
            'public_id' => (string) Str::ulid(),
            'requirement_id' => Requirement::factory(),
            'offer_id' => Offer::factory(),
            'provider_id' => Provider::factory(),
            'event_id' => Event::factory(),
            'agreed_total_ugx' => $totalUgx,
            'original_total_ugx' => $totalUgx,
            'status' => 'confirmed',
        ];
    }
}
