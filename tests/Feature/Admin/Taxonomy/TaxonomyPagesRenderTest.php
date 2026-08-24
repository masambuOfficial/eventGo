<?php

namespace Tests\Feature\Admin\Taxonomy;

use App\Domain\Catalog\Models\EventType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaxonomyPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_taxonomy_index_renders(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.index'));

        $response->assertOk();
        $response->assertSee('Event types');
    }

    public function test_event_types_screen_renders(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.event-types'));

        $response->assertOk();
    }

    public function test_service_categories_screen_renders(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.service-categories'));

        $response->assertOk();
    }

    public function test_districts_screen_renders(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.districts'));

        $response->assertOk();
    }

    public function test_scope_questions_screen_renders(): void
    {
        $eventType = EventType::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.scope-questions', $eventType));

        $response->assertOk();
    }

    public function test_requirement_templates_screen_renders(): void
    {
        $eventType = EventType::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('admin.taxonomy.requirement-templates', $eventType));

        $response->assertOk();
    }

    public function test_event_type_can_be_created(): void
    {
        Livewire::actingAs($this->admin())
            ->test(\App\Livewire\Admin\Taxonomy\EventTypes::class)
            ->set('name', 'Baby Shower')
            ->set('slug', 'baby-shower')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('event_types', ['slug' => 'baby-shower', 'name' => 'Baby Shower']);
    }

    public function test_service_category_cannot_be_nested_under_a_child(): void
    {
        $topLevel = \App\Domain\Catalog\Models\ServiceCategory::factory()->create(['parent_id' => null]);
        $child = \App\Domain\Catalog\Models\ServiceCategory::factory()->create(['parent_id' => $topLevel->id]);

        Livewire::actingAs($this->admin())
            ->test(\App\Livewire\Admin\Taxonomy\ServiceCategories::class)
            ->set('name', 'Too deep')
            ->set('slug', 'too-deep')
            ->set('parentId', $child->id)
            ->call('save')
            ->assertHasErrors('parentId');

        $this->assertDatabaseMissing('service_categories', ['slug' => 'too-deep']);
    }
}
