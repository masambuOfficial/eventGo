<?php

namespace Tests\Feature\Marketing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterIntentTest extends TestCase
{
    use RefreshDatabase;

    public function test_organiser_intent_changes_the_register_heading(): void
    {
        $response = $this->get(route('register', ['intent' => 'organiser']));

        $response->assertOk();
        $response->assertSee('Create your account to plan your event');
    }

    public function test_provider_intent_changes_the_register_heading(): void
    {
        $response = $this->get(route('register', ['intent' => 'provider']));

        $response->assertOk();
        $response->assertSee('Create your account to start offering your services');
    }

    public function test_no_intent_shows_the_generic_heading(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Create your account');
        $response->assertDontSee('to plan your event');
        $response->assertDontSee('to start offering your services');
    }
}
