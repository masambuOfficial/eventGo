<?php

namespace Tests\Feature\Billing;

use App\Domain\Providers\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Smoke coverage only — these are otherwise plain read-only pages this
 * codebase doesn't unit-test beyond confirming they render for the right
 * audience without error.
 */
class BillingPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_billing_page_renders(): void
    {
        $provider = Provider::factory()->create();

        $response = $this->actingAs($provider->owner)->get(route('provider.billing.index'));

        $response->assertOk();
        $response->assertSee('Billing');
    }

    public function test_admin_billing_activation_page_renders(): void
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.billing.index'));

        $response->assertOk();
        $response->assertSee('Billing activation');
    }
}
