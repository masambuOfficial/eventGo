<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Events\Models\Requirement;
use App\Domain\Sourcing\Models\Clarification;
use App\Domain\Sourcing\Models\Offer;
use App\Models\User;

class AskClarification
{
    public function __invoke(Requirement $requirement, User $askedBy, string $question, ?Offer $offer = null, bool $isPublic = true): Clarification
    {
        return Clarification::create([
            'requirement_id' => $requirement->id,
            'offer_id' => $offer?->id,
            'asked_by_user_id' => $askedBy->id,
            'question' => $question,
            'is_public' => $isPublic,
        ]);
    }
}
