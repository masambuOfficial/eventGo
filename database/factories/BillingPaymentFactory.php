<?php

namespace Database\Factories;

use App\Domain\Billing\Models\BillingPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingPayment>
 */
class BillingPaymentFactory extends Factory
{
    protected $model = BillingPayment::class;

    public function definition(): array
    {
        return [
            'amount_ugx' => fake()->numberBetween(50_000, 500_000),
            'channel' => 'manual',
            'gateway' => 'manual',
            // Unique per call — billing_payments has a unique index on
            // [gateway, gateway_ref]; forgetting fake()->unique() here is
            // an easy way to get a flaky suite.
            'gateway_ref' => fake()->unique()->uuid(),
            'status' => 'settled',
            'paid_at' => now(),
        ];
    }
}
