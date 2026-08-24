<?php

namespace Tests\Feature\Marketing;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedVisitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_sees_a_dashboard_link_instead_of_sign_up(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('marketing.home'));

        $response->assertOk();
        $response->assertSee('Go to dashboard');
        $response->assertDontSee('Sign up');
    }

    public function test_a_guest_sees_sign_up_not_a_dashboard_link(): void
    {
        $response = $this->get(route('marketing.home'));

        $response->assertOk();
        $response->assertSee('Sign up');
        $response->assertDontSee('Go to dashboard');
    }
}
