<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EquipmentControllerTest extends TestCase
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

    public function test_index_returns_location_name(): void
    {
        $location = Location::factory()->create(['name' => 'Headquarters']);
        Equipment::factory()->for($location->customer)->for($location)->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->getJson('/api/v1/equipment');

        $response->assertOk();
        $response->assertJsonPath('data.0.locationName', 'Headquarters');
    }

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/equipment')->assertForbidden();
    }

    public function test_store_derives_customer_id_from_the_location(): void
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->postJson('/api/v1/equipment', [
            'location_id' => $location->id,
            'type' => 'Rooftop Unit',
            'brand' => 'Carrier',
            'model' => 'WeatherMaker',
            'serial_number' => 'CAR-1',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.customerId', $customer->id);
        $this->assertDatabaseHas('equipment', [
            'serial_number' => 'CAR-1',
            'customer_id' => $customer->id,
        ]);
    }

    public function test_store_rejects_a_duplicate_serial_number(): void
    {
        $existing = Equipment::factory()->create(['serial_number' => 'DUPLICATE-1']);
        $location = Location::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/equipment', [
            'location_id' => $location->id,
            'type' => 'Rooftop Unit',
            'brand' => 'Carrier',
            'model' => 'WeatherMaker',
            'serial_number' => 'DUPLICATE-1',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('serial_number');
    }

    public function test_update_moving_equipment_to_another_location_updates_customer_id(): void
    {
        $equipment = Equipment::factory()->create();
        $newCustomer = Customer::factory()->create();
        $newLocation = Location::factory()->for($newCustomer)->create();

        $response = $this->actingAs($this->userWithRole('Admin'))
            ->putJson("/api/v1/equipment/{$equipment->id}", ['location_id' => $newLocation->id]);

        $response->assertOk();
        $response->assertJsonPath('data.customerId', $newCustomer->id);
        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'customer_id' => $newCustomer->id,
            'location_id' => $newLocation->id,
        ]);
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $equipment = Equipment::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/equipment/{$equipment->id}")->assertNoContent();

        $this->assertSoftDeleted($equipment);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $equipment = Equipment::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/equipment/{$equipment->id}")->assertForbidden();
    }
}
