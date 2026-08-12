<?php

namespace App\Livewire\Events;

use App\Domain\Catalog\Models\District;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ScopeQuestion;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Actions\CommitRequirements;
use App\Domain\Events\Actions\CreateEvent;
use App\Domain\Events\Actions\GenerateRequirements;
use App\Domain\Events\Actions\SaveScopeAnswers;
use App\Domain\Events\Models\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Plan your event')]
class Wizard extends Component
{
    public int $step = 1;

    public ?int $eventId = null;

    // Step 1 — basics
    public string $name = '';

    public ?int $event_type_id = null;

    public string $starts_at = '';

    public ?int $district_id = null;

    public string $venue_name = '';

    public ?int $guest_count_expected = null;

    // Step 2 — scope answers, keyed by scope_question_id
    public array $answers = [];

    // Step 3 — requirements matrix, list of editable line arrays
    public array $lines = [];

    public function mount(?Event $event = null): void
    {
        if (! $event) {
            return;
        }

        abort_unless($event->owner_user_id === auth()->id(), 403);

        $this->eventId = $event->id;
        $this->name = $event->name;
        $this->event_type_id = $event->event_type_id;
        $this->starts_at = $event->starts_at->format('Y-m-d');
        $this->district_id = $event->district_id;
        $this->venue_name = (string) $event->venue_name;
        $this->guest_count_expected = $event->guest_count_expected;

        $this->loadAnswers($event);
    }

    protected function event(): ?Event
    {
        return $this->eventId ? Event::find($this->eventId) : null;
    }

    public function districts(): Collection
    {
        return District::where('is_active', true)->orderBy('name')->get();
    }

    public function eventTypes(): Collection
    {
        return EventType::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function questions(): Collection
    {
        if (! $this->event_type_id) {
            return collect();
        }

        return ScopeQuestion::where('event_type_id', $this->event_type_id)->orderBy('sort_order')->get();
    }

    protected function loadAnswers(Event $event): void
    {
        $rows = DB::table('event_scope_answers')
            ->where('event_id', $event->id)
            ->pluck('value', 'scope_question_id');

        foreach ($rows as $questionId => $json) {
            $this->answers[$questionId] = json_decode($json, true);
        }
    }

    public function saveBasics(CreateEvent $createEvent): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'venue_name' => ['nullable', 'string', 'max:150'],
            'guest_count_expected' => ['nullable', 'integer', 'min:1'],
        ]);

        $event = $this->event();

        if (! $event) {
            $event = $createEvent(auth()->user(), $data);
            $this->eventId = $event->id;
        } else {
            $event->update($data);
        }

        // Event type may have changed — drop answers that no longer belong to it.
        $this->answers = [];
        $this->loadAnswers($event);

        $this->step = 2;
    }

    public function saveAnswers(SaveScopeAnswers $saveScopeAnswers): void
    {
        $event = $this->event();

        if (! $event) {
            $this->step = 1;

            return;
        }

        $questions = $this->questions();
        $rules = [];

        foreach ($questions as $question) {
            $rule = $question->is_required ? 'required' : 'nullable';
            $rules["answers.{$question->id}"] = match ($question->input_type) {
                'number' => [$rule, 'numeric'],
                'bool' => [$rule],
                'select' => [$rule, 'string'],
                'multiselect' => [$rule === 'required' ? 'required' : 'nullable', 'array'],
                'date' => [$rule, 'date'],
                default => [$rule, 'string'],
            };
        }

        $this->validate($rules);

        $answersToSave = [];
        foreach ($questions as $question) {
            $value = $this->answers[$question->id] ?? null;

            if ($question->input_type === 'number') {
                $value = $value === null || $value === '' ? null : (float) $value;
            } elseif ($question->input_type === 'bool') {
                $value = $value === null || $value === '' ? null : (bool) (int) $value;
            }

            if ($value !== null && $value !== '') {
                $answersToSave[$question->id] = $value;
            }
        }

        $saveScopeAnswers($event, $answersToSave);

        $this->lines = (new GenerateRequirements)($event)->toArray();

        $this->step = 3;
    }

    public function addCustomLine(): void
    {
        $this->lines[] = [
            'service_category_id' => '',
            'category_name' => '',
            'title' => '',
            'description' => null,
            'quantity' => 1,
            'unit' => null,
            'benchmark_unit_cost_ugx' => null,
            'budget_estimate_ugx' => null,
            'priority' => 'important',
            'include' => true,
            'source' => 'manual',
        ];
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function categories(): Collection
    {
        return ServiceCategory::whereNotNull('parent_id')->where('is_active', true)->orderBy('name')->get();
    }

    public function budgetTotal(): int
    {
        return collect($this->lines)
            ->filter(fn (array $line) => $line['include'] ?? true)
            ->sum(fn (array $line) => $line['budget_estimate_ugx'] ?? 0);
    }

    public function commit(CommitRequirements $commitRequirements): void
    {
        $event = $this->event();

        if (! $event) {
            $this->step = 1;

            return;
        }

        foreach ($this->lines as &$line) {
            if (($line['source'] ?? null) === 'manual' && filled($line['service_category_id'])) {
                $category = ServiceCategory::find($line['service_category_id']);
                $line['category_name'] = $category?->name;
                $line['title'] = $line['title'] ?: $category?->name;
            }
        }
        unset($line);

        $commitRequirements($event, $this->lines);

        $this->redirectRoute('events.dashboard', $event);
    }

    public function backTo(int $step): void
    {
        $this->step = $step;
    }

    public function render()
    {
        return view('livewire.events.wizard', [
            'districts' => $this->districts(),
            'eventTypes' => $this->eventTypes(),
            'questions' => $this->questions(),
            'categories' => $this->categories(),
        ]);
    }
}
