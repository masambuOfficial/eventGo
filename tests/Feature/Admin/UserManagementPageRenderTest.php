<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementPageRenderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_the_page_renders_and_lists_users(): void
    {
        User::factory()->create(['full_name' => 'Jane Organiser']);

        $response = $this->actingAs($this->admin())->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Jane Organiser');
    }

    public function test_search_filters_the_list(): void
    {
        User::factory()->create(['full_name' => 'Findable Person', 'email' => 'findable@example.com']);
        User::factory()->create(['full_name' => 'Someone Else', 'email' => 'else@example.com']);

        Livewire::actingAs($this->admin())
            ->test(\App\Livewire\Admin\UserManagement::class)
            ->set('search', 'Findable')
            ->assertSee('Findable Person')
            ->assertDontSee('Someone Else');
    }
}
