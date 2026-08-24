<?php

namespace Tests\Feature\Marketing;

use App\Domain\Billing\Models\Plan;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke coverage for the public marketing pages — matches this codebase's
 * habit of not exhaustively testing plain read-only Blade views.
 */
class MarketingPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        EventType::factory()->create(['name' => 'Wedding']);
        $category = ServiceCategory::factory()->create(['name' => 'Catering']);

        $response = $this->get(route('marketing.home'));

        $response->assertOk();
        $response->assertSee('Wedding');
        $response->assertSee('Catering');
    }

    public function test_for_organisers_page_renders(): void
    {
        EventType::factory()->create(['name' => 'Graduation Party']);

        $response = $this->get(route('marketing.organisers'));

        $response->assertOk();
        $response->assertSee('Graduation Party');
    }

    public function test_for_providers_page_renders(): void
    {
        ServiceCategory::factory()->create(['name' => 'Decoration']);

        $response = $this->get(route('marketing.providers'));

        $response->assertOk();
        $response->assertSee('Decoration');
    }

    public function test_pricing_page_shows_real_plan_data(): void
    {
        Plan::factory()->create([
            'code' => 'pro-30',
            'name' => 'Professional — 1 month',
            'price_ugx' => 60000,
            'is_active' => true,
            'audience' => 'provider',
        ]);

        $response = $this->get(route('marketing.pricing'));

        $response->assertOk();
        $response->assertSee('Professional — 1 month');
        $response->assertSee('60,000');
    }

    public function test_pricing_page_hides_inactive_plans(): void
    {
        Plan::factory()->create(['name' => 'Retired plan', 'is_active' => false, 'audience' => 'provider']);

        $response = $this->get(route('marketing.pricing'));

        $response->assertOk();
        $response->assertDontSee('Retired plan');
    }

    public function test_privacy_and_terms_pages_render(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Privacy');
        $this->get(route('legal.terms'))->assertOk()->assertSee('Terms');
    }

    public function test_marketing_layout_does_not_load_livewire_scripts(): void
    {
        $response = $this->get(route('marketing.home'));

        $response->assertOk();
        $response->assertDontSee('livewire.js', false);
    }
}
