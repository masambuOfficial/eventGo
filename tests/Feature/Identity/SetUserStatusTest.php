<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\Actions\SetUserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetUserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        (new SetUserStatus)($admin, $admin, 'suspended');
    }

    public function test_an_ordinary_suspend_succeeds(): void
    {
        $target = User::factory()->create();
        $admin = User::factory()->create();

        (new SetUserStatus)($target, $admin, 'suspended');

        $this->assertSame('suspended', $target->fresh()->status);
    }

    public function test_reactivating_a_suspended_user_succeeds(): void
    {
        $target = User::factory()->create();
        $target->forceFill(['status' => 'suspended'])->save();
        $admin = User::factory()->create();

        (new SetUserStatus)($target, $admin, 'active');

        $this->assertSame('active', $target->fresh()->status);
    }
}
