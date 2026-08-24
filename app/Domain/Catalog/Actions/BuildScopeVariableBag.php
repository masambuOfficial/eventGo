<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\ScopeQuestion;

/**
 * The default-typed variable bag `GenerateRequirements` evaluates
 * expressions against, keyed by `scope_questions.key` and shaped by
 * `input_type` — extracted here so the admin requirement-template editor
 * can validate an expression against the same whitelist `GenerateRequirements`
 * actually uses, rather than a second, potentially-drifting guess at it.
 */
class BuildScopeVariableBag
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(int $eventTypeId): array
    {
        $variables = [];

        foreach (ScopeQuestion::where('event_type_id', $eventTypeId)->get() as $question) {
            $variables[$question->key] = match ($question->input_type) {
                'number' => 0,
                'bool' => false,
                'multiselect' => [],
                default => '',
            };
        }

        return $variables;
    }
}
