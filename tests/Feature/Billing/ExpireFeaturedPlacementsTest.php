<?php

namespace Tests\Feature\Billing;

use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExpireFeaturedPlacementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clears_featured_until_once_it_is_past(): void
    {
        $expired = Provider::factory()->create(['featured_until' => now()->subDay()]);
        $stillFeatured = Provider::factory()->create(['featured_until' => now()->addDay()]);
        $neverFeatured = Provider::factory()->create(['featured_until' => null]);

        Artisan::call('featured-placements:expire');

        $this->assertNull($expired->fresh()->featured_until);
        $this->assertNotNull($stillFeatured->fresh()->featured_until);
        $this->assertNull($neverFeatured->fresh()->featured_until);
    }
}
