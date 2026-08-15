<?php

namespace App\Domain\Sourcing\Actions;

use App\Domain\Sourcing\Models\Clarification;
use App\Models\User;

class AnswerClarification
{
    public function __invoke(Clarification $clarification, User $answeredBy, string $answer): Clarification
    {
        $clarification->forceFill([
            'answer' => $answer,
            'answered_by_user_id' => $answeredBy->id,
            'answered_at' => now(),
        ])->save();

        return $clarification;
    }
}
