<?php

namespace Database\Factories;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Providers\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'subscriber_type' => 'provider',
            'subscriber_id' => Provider::factory(),
            'plan_id' => Plan::factory(),
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
            'status' => 'active',
        ];
    }
}
