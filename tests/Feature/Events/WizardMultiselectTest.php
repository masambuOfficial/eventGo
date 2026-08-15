<?php

namespace Tests\Feature\Events;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ScopeQuestion;
use App\Livewire\Events\Wizard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class WizardMultiselectTest extends TestCase
{
    use RefreshDatabase;

    private function multiselectQuestion(EventType $eventType, bool $required = false): ScopeQuestion
    {
        return ScopeQuestion::create([
            'event_type_id' => $eventType->id,
            'key' => 'entertainment',
            'label' => 'What entertainment do you want?',
            'input_type' => 'multiselect',
            'options' => ['DJ', 'Live band', 'MC'],
            'is_required' => $required,
        ]);
    }

    public function test_multiselect_answers_default_to_an_empty_array_after_step_one(): void
    {
        $user = User::factory()->create();
        $eventType = EventType::factory()->create();
        $question = $this->multiselectQuestion($eventType);

        $component = Livewire::actingAs($user)
            ->test(Wizard::class, ['event' => null])
            ->set('name', 'Test wedding')
            ->set('event_type_id', $eventType->id)
            ->set('starts_at', now()->addMonth()->format('Y-m-d'))
            ->call('saveBasics');

        $this->assertSame([], $component->get('answers')[$question->id]);
    }

    public function test_selecting_one_multiselect_option_does_not_select_the_others(): void
    {
        $user = User::factory()->create();
        $eventType = EventType::factory()->create();
        $question = $this->multiselectQuestion($eventType);

        $component = Livewire::actingAs($user)
            ->test(Wizard::class, ['event' => null])
            ->set('name', 'Test wedding')
            ->set('event_type_id', $eventType->id)
            ->set('starts_at', now()->addMonth()->format('Y-m-d'))
            ->call('saveBasics')
            ->set("answers.{$question->id}", ['DJ'])
            ->call('saveAnswers');

        $saved = DB::table('event_scope_answers')
            ->where('scope_question_id', $question->id)
            ->value('value');

        $this->assertSame(['DJ'], json_decode($saved, true));
    }

    public function test_a_required_multiselect_left_empty_shows_a_friendly_message(): void
    {
        $user = User::factory()->create();
        $eventType = EventType::factory()->create();
        $this->multiselectQuestion($eventType, required: true);

        Livewire::actingAs($user)
            ->test(Wizard::class, ['event' => null])
            ->set('name', 'Test wedding')
            ->set('event_type_id', $eventType->id)
            ->set('starts_at', now()->addMonth()->format('Y-m-d'))
            ->call('saveBasics')
            ->call('saveAnswers')
            ->assertSee('Select at least one option for "What entertainment do you want?"')
            ->assertDontSee('must be an array');
    }
}
