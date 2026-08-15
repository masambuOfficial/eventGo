<?php

namespace Tests\Feature\Events;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Events\Models\Event;
use App\Domain\Events\Models\Requirement;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_renders_requirements_with_state_machine_backed_status(): void
    {
        $owner = User::factory()->create();
        $event = Event::factory()->create(['event_type_id' => EventType::factory(), 'owner_user_id' => $owner->id]);
        Requirement::factory()->create([
            'event_id' => $event->id,
            'service_category_id' => ServiceCategory::factory(),
            'status' => 'sourcing',
        ]);

        $response = $this->actingAs($owner)->get(route('events.dashboard', $event));

        $response->assertOk();
        $response->assertSee('Sourcing');
    }
}
