<?php

namespace Tests\Feature\Catalog;

use App\Domain\Catalog\Actions\BuildScopeVariableBag;
use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\ScopeQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildScopeVariableBagTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_types_match_input_type(): void
    {
        $eventType = EventType::factory()->create();

        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'guests', 'label' => 'Guests', 'input_type' => 'number', 'sort_order' => 1]);
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'outdoor', 'label' => 'Outdoor', 'input_type' => 'bool', 'sort_order' => 2]);
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'extras', 'label' => 'Extras', 'input_type' => 'multiselect', 'sort_order' => 3]);
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'venue', 'label' => 'Venue', 'input_type' => 'text', 'sort_order' => 4]);
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'season', 'label' => 'Season', 'input_type' => 'select', 'sort_order' => 5]);
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'date_needed', 'label' => 'Date', 'input_type' => 'date', 'sort_order' => 6]);

        $bag = (new BuildScopeVariableBag)($eventType->id);

        $this->assertSame(0, $bag['guests']);
        $this->assertFalse($bag['outdoor']);
        $this->assertSame([], $bag['extras']);
        $this->assertSame('', $bag['venue']);
        $this->assertSame('', $bag['season']);
        $this->assertSame('', $bag['date_needed']);
    }

    public function test_only_includes_questions_for_the_given_event_type(): void
    {
        $eventType = EventType::factory()->create();
        $otherEventType = EventType::factory()->create();

        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'guests', 'label' => 'Guests', 'input_type' => 'number', 'sort_order' => 1]);
        ScopeQuestion::create(['event_type_id' => $otherEventType->id, 'key' => 'budget', 'label' => 'Budget', 'input_type' => 'number', 'sort_order' => 1]);

        $bag = (new BuildScopeVariableBag)($eventType->id);

        $this->assertArrayHasKey('guests', $bag);
        $this->assertArrayNotHasKey('budget', $bag);
    }
}
