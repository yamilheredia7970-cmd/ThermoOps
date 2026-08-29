<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\MaintenancePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MaintenancePlansControllerTest extends TestCase
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

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/maintenance-plans')->assertForbidden();
    }

    public function test_index_returns_customer_name_and_equipment_count(): void
    {
        $customer = Customer::factory()->create(['name' => 'Nova Logistics']);
        $location = Location::factory()->for($customer)->create();
        $equipment = Equipment::factory()->for($customer)->for($location)->create();
        $plan = MaintenancePlan::factory()->for($customer)->create();
        $plan->equipment()->attach($equipment);

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->getJson('/api/v1/maintenance-plans');

        $response->assertOk();
        $response->assertJsonPath('data.0.customerName', 'Nova Logistics');
        $response->assertJsonPath('data.0.equipmentCount', 1);
    }

    public function test_store_creates_a_plan_and_attaches_equipment(): void
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $equipment = Equipment::factory()->for($customer)->for($location)->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/maintenance-plans', [
            'customer_id' => $customer->id,
            'plan_name' => 'Premium Commercial Care',
            'frequency' => 'Quarterly',
            'next_service' => now()->addMonths(3)->toDateString(),
            'equipment_ids' => [$equipment->id],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.equipmentCount', 1);
        $response->assertJsonPath('data.status', 'Pending');
    }

    public function test_store_rejects_equipment_belonging_to_a_different_customer(): void
    {
        $customer = Customer::factory()->create();
        $foreignEquipment = Equipment::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/maintenance-plans', [
            'customer_id' => $customer->id,
            'plan_name' => 'Premium Commercial Care',
            'frequency' => 'Quarterly',
            'next_service' => now()->addMonths(3)->toDateString(),
            'equipment_ids' => [$foreignEquipment->id],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('equipment_ids');
    }

    public function test_update_resyncs_equipment(): void
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        $oldEquipment = Equipment::factory()->for($customer)->for($location)->create();
        $newEquipment = Equipment::factory()->for($customer)->for($location)->create();
        $plan = MaintenancePlan::factory()->for($customer)->create();
        $plan->equipment()->attach($oldEquipment);

        $response = $this->actingAs($this->userWithRole('Admin'))->putJson("/api/v1/maintenance-plans/{$plan->id}", [
            'equipment_ids' => [$newEquipment->id],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.equipmentCount', 1);
        $this->assertDatabaseHas('maintenance_plan_equipment', ['maintenance_plan_id' => $plan->id, 'equipment_id' => $newEquipment->id]);
        $this->assertDatabaseMissing('maintenance_plan_equipment', ['maintenance_plan_id' => $plan->id, 'equipment_id' => $oldEquipment->id]);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $plan = MaintenancePlan::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/maintenance-plans/{$plan->id}")->assertForbidden();
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $plan = MaintenancePlan::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/maintenance-plans/{$plan->id}")->assertNoContent();

        $this->assertSoftDeleted($plan);
    }
}
