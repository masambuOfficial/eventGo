<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Catalog\Actions\BuildRequirementExpressionLanguage;
use App\Domain\Catalog\Actions\BuildScopeVariableBag;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\RequirementTemplate;
use App\Domain\Catalog\Models\ServiceCategory;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The one taxonomy screen with a real invariant: quantity_expression and
 * condition_expr are live symfony/expression-language strings evaluated by
 * GenerateRequirements for every organiser hitting this event type +
 * category. A bad expression here doesn't just corrupt a row — it breaks
 * the wizard live. Validated by actually evaluating it against the same
 * variable bag and function whitelist GenerateRequirements uses.
 */
#[Title('Requirement templates')]
class RequirementTemplates extends Component
{
    public int $eventTypeId;

    public ?int $editingId = null;

    public ?int $serviceCategoryId = null;

    public string $conditionExpr = '';

    public string $quantityExpression = '1';

    public string $benchmarkUnitCostUgx = '';

    public string $defaultTitle = '';

    public string $defaultNotes = '';

    public string $priority = 'important';

    public int $sortOrder = 0;

    public function mount(EventType $eventType): void
    {
        $this->eventTypeId = $eventType->id;
    }

    protected function eventType(): EventType
    {
        return EventType::findOrFail($this->eventTypeId);
    }

    public function items(): Collection
    {
        return RequirementTemplate::with('category')
            ->where('event_type_id', $this->eventTypeId)
            ->orderBy('sort_order')
            ->get();
    }

    public function categories(): Collection
    {
        return ServiceCategory::orderBy('name')->get();
    }

    public function edit(int $id): void
    {
        $template = RequirementTemplate::findOrFail($id);

        $this->editingId = $template->id;
        $this->serviceCategoryId = $template->service_category_id;
        $this->conditionExpr = (string) $template->condition_expr;
        $this->quantityExpression = $template->quantity_expression;
        $this->benchmarkUnitCostUgx = $template->benchmark_unit_cost_ugx !== null ? (string) $template->benchmark_unit_cost_ugx : '';
        $this->defaultTitle = (string) $template->default_title;
        $this->defaultNotes = (string) $template->default_notes;
        $this->priority = $template->priority;
        $this->sortOrder = $template->sort_order;
    }

    public function cancel(): void
    {
        $this->reset([
            'editingId', 'serviceCategoryId', 'conditionExpr', 'defaultTitle',
            'defaultNotes', 'benchmarkUnitCostUgx', 'sortOrder',
        ]);
        $this->quantityExpression = '1';
        $this->priority = 'important';
    }

    public function save(BuildScopeVariableBag $buildVariableBag, BuildRequirementExpressionLanguage $buildExpressionLanguage): void
    {
        $this->validate([
            'serviceCategoryId' => ['required', 'exists:service_categories,id'],
            'quantityExpression' => ['required', 'string', 'max:255'],
            'conditionExpr' => ['nullable', 'string', 'max:255'],
            'benchmarkUnitCostUgx' => ['nullable', 'integer', 'min:0'],
            'defaultTitle' => ['nullable', 'string', 'max:150'],
            'defaultNotes' => ['nullable', 'string', 'max:500'],
            'priority' => ['required', 'in:essential,important,optional'],
            'sortOrder' => ['required', 'integer'],
        ]);

        $variables = $buildVariableBag($this->eventTypeId);
        $language = $buildExpressionLanguage();

        try {
            $language->evaluate($this->quantityExpression, $variables);
        } catch (\Throwable $e) {
            $this->addError('quantityExpression', "This expression doesn't evaluate against this event type's scope questions: {$e->getMessage()}");

            return;
        }

        if ($this->conditionExpr) {
            try {
                $language->evaluate($this->conditionExpr, $variables);
            } catch (\Throwable $e) {
                $this->addError('conditionExpr', "This expression doesn't evaluate against this event type's scope questions: {$e->getMessage()}");

                return;
            }
        }

        $existing = RequirementTemplate::where('event_type_id', $this->eventTypeId)
            ->where('service_category_id', $this->serviceCategoryId)
            ->where('id', '!=', $this->editingId ?: 0)
            ->exists();

        if ($existing) {
            $this->addError('serviceCategoryId', 'This event type already has a template for that category.');

            return;
        }

        RequirementTemplate::updateOrCreate(
            ['id' => $this->editingId],
            [
                'event_type_id' => $this->eventTypeId,
                'service_category_id' => $this->serviceCategoryId,
                'condition_expr' => $this->conditionExpr ?: null,
                'quantity_expression' => $this->quantityExpression,
                'benchmark_unit_cost_ugx' => $this->benchmarkUnitCostUgx !== '' ? (int) $this->benchmarkUnitCostUgx : null,
                'default_title' => $this->defaultTitle ?: null,
                'default_notes' => $this->defaultNotes ?: null,
                'priority' => $this->priority,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->cancel();
    }

    public function toggleActive(int $id): void
    {
        $template = RequirementTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.requirement-templates', [
            'eventType' => $this->eventType(),
            'items' => $this->items(),
            'categories' => $this->categories(),
        ]);
    }
}
