<?php

namespace Tests\Feature\Admin\Taxonomy;

use App\Domain\Catalog\Models\EventType;
use App\Domain\Catalog\Models\RequirementTemplate;
use App\Domain\Catalog\Models\ScopeQuestion;
use App\Domain\Catalog\Models\ServiceCategory;
use App\Livewire\Admin\Taxonomy\RequirementTemplates;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RequirementTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_a_valid_expression_saves(): void
    {
        $eventType = EventType::factory()->create();
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'guest_count', 'label' => 'Guests', 'input_type' => 'number', 'sort_order' => 1]);
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(RequirementTemplates::class, ['eventType' => $eventType])
            ->set('serviceCategoryId', $category->id)
            ->set('quantityExpression', 'ceil(guest_count * 1.05)')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('requirement_templates', [
            'event_type_id' => $eventType->id,
            'service_category_id' => $category->id,
            'quantity_expression' => 'ceil(guest_count * 1.05)',
        ]);
    }

    public function test_an_expression_referencing_an_unknown_variable_is_rejected_and_not_saved(): void
    {
        $eventType = EventType::factory()->create();
        ScopeQuestion::create(['event_type_id' => $eventType->id, 'key' => 'guest_count', 'label' => 'Guests', 'input_type' => 'number', 'sort_order' => 1]);
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(RequirementTemplates::class, ['eventType' => $eventType])
            ->set('serviceCategoryId', $category->id)
            ->set('quantityExpression', 'ceil(nonexistent_field * 2)')
            ->call('save')
            ->assertHasErrors('quantityExpression');

        $this->assertDatabaseMissing('requirement_templates', [
            'event_type_id' => $eventType->id,
            'service_category_id' => $category->id,
        ]);
    }

    public function test_a_syntactically_invalid_expression_is_rejected(): void
    {
        $eventType = EventType::factory()->create();
        $category = ServiceCategory::factory()->create();

        Livewire::actingAs($this->admin())
            ->test(RequirementTemplates::class, ['eventType' => $eventType])
            ->set('serviceCategoryId', $category->id)
            ->set('quantityExpression', 'ceil(((')
            ->call('save')
            ->assertHasErrors('quantityExpression');
    }

    public function test_a_duplicate_category_pairing_is_rejected_with_a_friendly_error(): void
    {
        $eventType = EventType::factory()->create();
        $category = ServiceCategory::factory()->create();

        RequirementTemplate::create([
            'event_type_id' => $eventType->id,
            'service_category_id' => $category->id,
            'quantity_expression' => '1',
        ]);

        Livewire::actingAs($this->admin())
            ->test(RequirementTemplates::class, ['eventType' => $eventType])
            ->set('serviceCategoryId', $category->id)
            ->set('quantityExpression', '2')
            ->call('save')
            ->assertHasErrors('serviceCategoryId');

        $this->assertSame(1, RequirementTemplate::where('event_type_id', $eventType->id)->where('service_category_id', $category->id)->count());
    }

    public function test_editing_the_existing_template_does_not_trip_the_duplicate_check_on_itself(): void
    {
        $eventType = EventType::factory()->create();
        $category = ServiceCategory::factory()->create();

        $template = RequirementTemplate::create([
            'event_type_id' => $eventType->id,
            'service_category_id' => $category->id,
            'quantity_expression' => '1',
        ]);

        Livewire::actingAs($this->admin())
            ->test(RequirementTemplates::class, ['eventType' => $eventType])
            ->call('edit', $template->id)
            ->set('quantityExpression', '2')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('2', $template->fresh()->quantity_expression);
    }
}
