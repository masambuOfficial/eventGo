<?php

namespace App\Domain\Identity\Actions;

use App\Models\User;
use RuntimeException;

/**
 * Two self-lockout hazards guarded here, not in the Livewire component:
 * an admin revoking their own access, and a different admin revoking the
 * platform's last remaining admin. Either would leave the console
 * administratively unreachable.
 */
class ToggleAdminRole
{
    public function __invoke(User $target, User $actingUser): User
    {
        $hasRole = $target->hasRole('admin');

        if ($hasRole) {
            if ($target->is($actingUser)) {
                throw new RuntimeException('You cannot remove your own admin access.');
            }

            if (User::role('admin')->count() <= 1) {
                throw new RuntimeException('This is the last remaining admin — grant another user admin access first.');
            }

            $target->removeRole('admin');
        } else {
            $target->assignRole('admin');
        }

        return $target->refresh();
    }
}
