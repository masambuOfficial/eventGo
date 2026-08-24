<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuspendedUserCannotLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_suspended_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $user->forceFill(['status' => 'suspended'])->save();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_active_user_can_log_in(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_still_shows_the_generic_failure_not_status_info(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $user->forceFill(['status' => 'suspended'])->save();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
