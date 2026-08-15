<?php

namespace Tests\Feature\Sourcing;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Sourcing\Actions\AnswerClarification;
use App\Domain\Sourcing\Actions\AskClarification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClarificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_question_can_be_asked_and_then_answered(): void
    {
        $event = Event::factory()->create(['event_type_id' => EventType::factory()]);
        $requirement = Requirement::factory()->create(['event_id' => $event->id, 'service_category_id' => ServiceCategory::factory()]);
        $asker = User::factory()->create();
        $answerer = User::factory()->create();

        $clarification = (new AskClarification)($requirement, $asker, 'Is parking available on site?');

        $this->assertNull($clarification->answer);

        (new AnswerClarification)($clarification, $answerer, 'Yes, for up to 40 cars.');

        $this->assertSame('Yes, for up to 40 cars.', $clarification->fresh()->answer);
        $this->assertSame($answerer->id, $clarification->fresh()->answered_by_user_id);
        $this->assertNotNull($clarification->fresh()->answered_at);
    }
}
