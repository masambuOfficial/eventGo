<?php

namespace Tests\Feature\Reporting;

use App\Domain\Providers\Models\Provider;
use App\Domain\Providers\Models\ProviderVerification;
use App\Domain\Reporting\Queries\OperationalMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_pending_verifications_and_computes_median_clearance_time(): void
    {
        $provider = Provider::factory()->create();

        ProviderVerification::create([
            'provider_id' => $provider->id,
            'tier' => 1,
            'evidence_type' => 'social_page',
            'status' => 'pending',
        ]);

        $resolved = ProviderVerification::create([
            'provider_id' => $provider->id,
            'tier' => 1,
            'evidence_type' => 'social_page',
        ]);
        // status isn't fillable (set only via the approve/reject actions in
        // real usage) — forceFill it directly for the test.
        $resolved->forceFill(['status' => 'approved', 'created_at' => now()->subHours(20), 'reviewed_at' => now()])->save();

        $result = (new OperationalMetrics)();

        $this->assertSame(1, $result['verification_queue_depth']);
        $this->assertSame(20.0, $result['median_verification_clearance_hours']);
    }

    public function test_averages_provider_response_rate_and_medians_response_time(): void
    {
        Provider::factory()->create(['is_active' => true, 'response_rate' => 80, 'median_response_minutes' => 30]);
        Provider::factory()->create(['is_active' => true, 'response_rate' => 60, 'median_response_minutes' => 90]);
        Provider::factory()->create(['is_active' => false, 'response_rate' => 10, 'median_response_minutes' => 5]);

        $result = (new OperationalMetrics)();

        $this->assertSame(70.0, $result['average_provider_response_rate']);
        $this->assertSame(60.0, $result['median_provider_response_minutes']);
    }
}
