<?php

namespace App\Domain\Identity\Actions;

use App\Models\User;
use RuntimeException;

class SetUserStatus
{
    public function __invoke(User $target, User $actingUser, string $status): User
    {
        if (! in_array($status, ['active', 'suspended'], true)) {
            throw new RuntimeException('Invalid status.');
        }

        if ($target->is($actingUser) && $status === 'suspended') {
            throw new RuntimeException('You cannot suspend your own account.');
        }

        $target->forceFill(['status' => $status])->save();

        return $target;
    }
}
