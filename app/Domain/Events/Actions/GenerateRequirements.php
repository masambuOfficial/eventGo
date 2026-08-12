<?php

namespace App\Domain\Events\Actions;

use App\Domain\Catalog\Models\RequirementTemplate;
use App\Domain\Catalog\Models\ScopeQuestion;
use App\Domain\Events\Models\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Turns scope answers into a suggested, editable requirements list. Never
 * writes to the database — the organiser reviews and edits before
 * CommitRequirements persists anything. Architecture §6.1 step 4.
 */
class GenerateRequirements
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function __invoke(Event $event): Collection
    {
        $variables = $this->buildVariableBag($event);
        $language = $this->buildExpressionLanguage();

        return RequirementTemplate::with('category')
            ->where('event_type_id', $event->event_type_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function (RequirementTemplate $template) use ($language, $variables) {
                if (! $template->condition_expr) {
                    return true;
                }

                try {
                    return (bool) $language->evaluate($template->condition_expr, $variables);
                } catch (\Throwable $e) {
                    Log::warning('Requirement template condition failed to evaluate', [
                        'template_id' => $template->id,
                        'expression' => $template->condition_expr,
                        'error' => $e->getMessage(),
                    ]);

                    return false;
                }
            })
            ->map(function (RequirementTemplate $template) use ($language, $variables) {
                try {
                    $quantity = (float) $language->evaluate($template->quantity_expression, $variables);
                } catch (\Throwable $e) {
                    Log::warning('Requirement template quantity failed to evaluate', [
                        'template_id' => $template->id,
                        'expression' => $template->quantity_expression,
                        'error' => $e->getMessage(),
                    ]);
                    $quantity = 1.0;
                }

                $budgetEstimate = $template->benchmark_unit_cost_ugx
                    ? (int) round($quantity * $template->benchmark_unit_cost_ugx)
                    : null;

                return [
                    'service_category_id' => $template->service_category_id,
                    'category_name' => $template->category->name,
                    'title' => $template->default_title ?: $template->category->name,
                    'description' => $template->default_notes,
                    'quantity' => $quantity,
                    'unit' => $template->category->unit_label,
                    'benchmark_unit_cost_ugx' => $template->benchmark_unit_cost_ugx,
                    'budget_estimate_ugx' => $budgetEstimate,
                    'priority' => $template->priority,
                    'include' => true,
                ];
            })
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildVariableBag(Event $event): array
    {
        $questions = ScopeQuestion::where('event_type_id', $event->event_type_id)->get();

        $variables = [];

        foreach ($questions as $question) {
            $variables[$question->key] = match ($question->input_type) {
                'number' => 0,
                'bool' => false,
                'multiselect' => [],
                default => '',
            };
        }

        $answers = DB::table('event_scope_answers')
            ->join('scope_questions', 'scope_questions.id', '=', 'event_scope_answers.scope_question_id')
            ->where('event_scope_answers.event_id', $event->id)
            ->pluck('event_scope_answers.value', 'scope_questions.key');

        foreach ($answers as $key => $json) {
            $variables[$key] = json_decode($json, true);
        }

        return $variables;
    }

    private function buildExpressionLanguage(): ExpressionLanguage
    {
        $language = new ExpressionLanguage();

        foreach (['ceil', 'floor', 'round', 'min', 'max'] as $function) {
            $language->addFunction(ExpressionFunction::fromPhp($function));
        }

        return $language;
    }
}
