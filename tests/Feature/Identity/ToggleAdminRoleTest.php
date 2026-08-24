<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\Actions\ToggleAdminRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ToggleAdminRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    public function test_an_admin_cannot_revoke_their_own_access(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $other = User::factory()->create();
        $other->assignRole('admin');

        $this->expectException(\RuntimeException::class);

        (new ToggleAdminRole)($admin, $admin);
    }

    public function test_revoking_the_last_admin_from_another_account_is_blocked(): void
    {
        $onlyAdmin = User::factory()->create();
        $onlyAdmin->assignRole('admin');
        $actingUser = User::factory()->create();

        $this->expectException(\RuntimeException::class);

        (new ToggleAdminRole)($onlyAdmin, $actingUser);
    }

    public function test_an_ordinary_revoke_succeeds_when_another_admin_remains(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $actingAdmin = User::factory()->create();
        $actingAdmin->assignRole('admin');

        (new ToggleAdminRole)($admin, $actingAdmin);

        $this->assertFalse($admin->fresh()->hasRole('admin'));
    }

    public function test_granting_admin_to_a_non_admin_succeeds(): void
    {
        $user = User::factory()->create();
        $actingAdmin = User::factory()->create();
        $actingAdmin->assignRole('admin');

        (new ToggleAdminRole)($user, $actingAdmin);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }
}
