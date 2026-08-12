<?php

namespace App\Domain\Providers\Actions;

use App\Domain\Providers\Models\ProviderVerification;
use App\Models\User;

class RejectProviderVerification
{
    public function __invoke(ProviderVerification $verification, User $reviewer, ?string $notes = null): void
    {
        $verification->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'notes' => $notes ?: $verification->notes,
        ])->save();
    }
}
