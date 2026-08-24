<?php

namespace Tests\Feature\Reporting;

use App\Domain\Billing\Models\BillingPayment;
use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Reporting\Queries\RevenueMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_no_data_below_the_minimum_floor(): void
    {
        Subscription::factory()->count(2)->create();

        $metrics = new RevenueMetrics;

        $this->assertFalse($metrics->hasEnoughData());
        $this->assertSame([], $metrics());
    }

    public function test_reports_real_numbers_once_the_floor_is_met(): void
    {
        $plan = Plan::factory()->create();
        $subscriptions = Subscription::factory()->count(5)->create(['plan_id' => $plan->id, 'status' => 'active', 'expires_at' => now()->addMonth()]);

        foreach ($subscriptions as $subscription) {
            BillingPayment::factory()->create(['subscription_id' => $subscription->id, 'amount_ugx' => 60000, 'status' => 'settled']);
        }

        $metrics = new RevenueMetrics;

        $this->assertTrue($metrics->hasEnoughData());
        $result = $metrics();

        $this->assertSame(5, $result['total_subscriptions_sold']);
        $this->assertSame(5, $result['currently_active_subscriptions']);
        $this->assertSame(300000, $result['total_revenue_ugx']);
    }
}
