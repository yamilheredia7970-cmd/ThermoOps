<?php

namespace Tests\Feature\Api;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ToolsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_index_is_visible_to_a_technician(): void
    {
        Tool::factory()->create();

        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/tools')->assertOk();
    }

    public function test_show_returns_the_assigned_technicians_name(): void
    {
        $technician = $this->userWithRole('Technician');
        $tool = Tool::factory()->create(['status' => 'Assigned', 'assigned_to' => $technician->id]);

        $response = $this->actingAs($this->userWithRole('Admin'))->getJson("/api/v1/tools/{$tool->id}");

        $response->assertOk();
        $response->assertJsonPath('data.assignedTo', $technician->name);
    }

    public function test_store_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->postJson('/api/v1/tools', [
            'name' => 'Test Tool', 'brand' => 'Acme', 'category' => 'Electrical',
        ])->assertForbidden();
    }

    public function test_store_creates_an_available_tool_for_a_dispatcher(): void
    {
        $response = $this->actingAs($this->userWithRole('Dispatcher'))->postJson('/api/v1/tools', [
            'name' => 'Test Tool', 'brand' => 'Acme', 'category' => 'Electrical',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'Available');
    }

    public function test_store_rejects_assigned_status_without_a_technician(): void
    {
        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/tools', [
            'name' => 'Test Tool', 'brand' => 'Acme', 'category' => 'Electrical', 'status' => 'Assigned',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('assigned_to');
    }

    public function test_store_rejects_a_technician_without_the_assigned_status(): void
    {
        $technician = $this->userWithRole('Technician');

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/tools', [
            'name' => 'Test Tool', 'brand' => 'Acme', 'category' => 'Electrical', 'assigned_to' => $technician->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('status');
    }

    public function test_store_rejects_assigning_a_tool_to_a_non_technician(): void
    {
        $dispatcher = $this->userWithRole('Dispatcher');

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/tools', [
            'name' => 'Test Tool', 'brand' => 'Acme', 'category' => 'Electrical',
            'status' => 'Assigned', 'assigned_to' => $dispatcher->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('assigned_to');
    }

    public function test_update_can_reassign_a_tool_to_another_technician(): void
    {
        $tool = Tool::factory()->create(['status' => 'Available']);
        $technician = $this->userWithRole('Technician');

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/tools/{$tool->id}", [
            'status' => 'Assigned', 'assigned_to' => $technician->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.assignedToId', $technician->id);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $tool = Tool::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/tools/{$tool->id}")->assertForbidden();
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $tool = Tool::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/tools/{$tool->id}")->assertNoContent();

        $this->assertSoftDeleted($tool);
    }
}
