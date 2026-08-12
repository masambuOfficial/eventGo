<?php

namespace App\Domain\Events\Actions;

use App\Domain\Events\Models\Event;
use Illuminate\Support\Facades\DB;

class SaveScopeAnswers
{
    /**
     * @param  array<int, mixed>  $answersByQuestionId
     */
    public function __invoke(Event $event, array $answersByQuestionId): void
    {
        if (empty($answersByQuestionId)) {
            return;
        }

        $rows = [];

        foreach ($answersByQuestionId as $questionId => $value) {
            $rows[] = [
                'event_id' => $event->id,
                'scope_question_id' => $questionId,
                'value' => json_encode($value),
                'updated_at' => now(),
            ];
        }

        DB::table('event_scope_answers')->upsert($rows, ['event_id', 'scope_question_id'], ['value', 'updated_at']);
    }
}
