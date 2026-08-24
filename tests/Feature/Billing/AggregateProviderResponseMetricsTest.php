<?php

namespace Tests\Feature\Billing;

use App\Domain\Attribution\Models\ProviderLead;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AggregateProviderResponseMetricsTest extends TestCase
{
    use RefreshDatabase;

    // provider_leads has a unique (provider_id, requirement_id) — every
    // lead in these tests needs its own requirement.
    private function requirement(): Requirement
    {
        return Requirement::factory()->create([
            'event_id' => Event::factory()->create(['event_type_id' => EventType::factory()])->id,
            'service_category_id' => ServiceCategory::factory(),
        ]);
    }

    private function lead(Provider $provider, array $attributes): ProviderLead
    {
        return ProviderLead::create(array_merge([
            'provider_id' => $provider->id,
            'requirement_id' => $this->requirement()->id,
            'source' => 'opportunity_match',
            'outcome' => 'pending',
        ], $attributes));
    }

    public function test_computes_response_rate_and_median_response_time_from_viewed_to_offered(): void
    {
        $provider = Provider::factory()->create();

        // Viewed and offered 10 minutes later.
        $this->lead($provider, [
            'viewed_at' => now()->subDays(1)->subMinutes(10),
            'offered_at' => now()->subDays(1),
        ]);

        // Viewed and offered 30 minutes later.
        $this->lead($provider, [
            'viewed_at' => now()->subDays(2)->subMinutes(30),
            'offered_at' => now()->subDays(2),
        ]);

        // Viewed but never offered — counts against response_rate but has
        // no minute-diff to contribute to the median.
        $this->lead($provider, [
            'viewed_at' => now()->subDays(3),
            'offered_at' => null,
        ]);

        // Never viewed at all — engagement gap, excluded from both stats.
        $this->lead($provider, [
            'viewed_at' => null,
            'offered_at' => null,
        ]);

        Artisan::call('providers:aggregate-response-metrics');

        $provider->refresh();
        // 2 of 3 viewed leads were offered.
        $this->assertEqualsWithDelta(66.67, (float) $provider->response_rate, 0.01);
        $this->assertSame(20, $provider->median_response_minutes);
    }

    public function test_bookings_won_is_a_lifetime_count_not_windowed(): void
    {
        $provider = Provider::factory()->create();

        $this->lead($provider, [
            'viewed_at' => now()->subDays(200),
            'offered_at' => now()->subDays(199),
            'outcome' => 'won',
        ]);

        $this->lead($provider, [
            'viewed_at' => now()->subDays(1),
            'offered_at' => now(),
            'outcome' => 'won',
        ]);

        Artisan::call('providers:aggregate-response-metrics');

        $this->assertSame(2, $provider->fresh()->bookings_won);
    }
}
