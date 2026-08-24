<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ScopeQuestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Scope questions')]
class ScopeQuestions extends Component
{
    public int $eventTypeId;

    public ?int $editingId = null;

    public string $key = '';

    public string $label = '';

    public string $helpText = '';

    public string $inputType = 'text';

    public string $optionsText = '';

    public string $defaultValue = '';

    public bool $isRequired = false;

    public int $sortOrder = 0;

    public ?string $deleteError = null;

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
        return ScopeQuestion::where('event_type_id', $this->eventTypeId)->orderBy('sort_order')->get();
    }

    public function edit(int $id): void
    {
        $question = ScopeQuestion::findOrFail($id);

        $this->editingId = $question->id;
        $this->key = $question->key;
        $this->label = $question->label;
        $this->helpText = (string) $question->help_text;
        $this->inputType = $question->input_type;
        $this->optionsText = $question->options ? implode("\n", $question->options) : '';
        $this->defaultValue = (string) $question->default_value;
        $this->isRequired = $question->is_required;
        $this->sortOrder = $question->sort_order;
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'key', 'label', 'helpText', 'optionsText', 'defaultValue', 'isRequired', 'sortOrder']);
        $this->inputType = 'text';
    }

    public function save(): void
    {
        $this->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:scope_questions,key,'.($this->editingId ?: 'NULL').',id,event_type_id,'.$this->eventTypeId],
            'label' => ['required', 'string', 'max:200'],
            'helpText' => ['nullable', 'string', 'max:255'],
            'inputType' => ['required', 'in:number,bool,select,multiselect,text,date'],
            'defaultValue' => ['nullable', 'string', 'max:120'],
            'sortOrder' => ['required', 'integer'],
        ], [
            'key.regex' => 'Use lowercase letters, numbers, and underscores only.',
        ]);

        $options = null;

        if (in_array($this->inputType, ['select', 'multiselect'], true)) {
            $options = collect(explode("\n", $this->optionsText))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();

            if (empty($options)) {
                $this->addError('optionsText', 'List at least one option, one per line.');

                return;
            }
        }

        ScopeQuestion::updateOrCreate(
            ['id' => $this->editingId],
            [
                'event_type_id' => $this->eventTypeId,
                'key' => $this->key,
                'label' => $this->label,
                'help_text' => $this->helpText ?: null,
                'input_type' => $this->inputType,
                'options' => $options,
                'default_value' => $this->defaultValue ?: null,
                'is_required' => $this->isRequired,
                'sort_order' => $this->sortOrder,
            ]
        );

        $this->cancel();
    }

    /**
     * No is_active column exists on this table (unlike the other four
     * taxonomy models), and its FK to event_scope_answers cascades on
     * delete — so a hard delete would silently destroy organisers'
     * historical answers. Guarded in code instead of a schema change:
     * refuse once any event has actually answered it.
     */
    public function delete(int $id): void
    {
        $this->deleteError = null;

        $question = ScopeQuestion::where('id', $id)->where('event_type_id', $this->eventTypeId)->first();

        if (! $question) {
            return;
        }

        if (DB::table('event_scope_answers')->where('scope_question_id', $id)->exists()) {
            $this->deleteError = 'This question already has organiser answers recorded against it and cannot be removed.';

            return;
        }

        $question->delete();
    }

    public function render()
    {
        return view('livewire.admin.taxonomy.scope-questions', [
            'eventType' => $this->eventType(),
            'items' => $this->items(),
        ]);
    }
}
