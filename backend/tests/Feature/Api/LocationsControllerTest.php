<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LocationsControllerTest extends TestCase
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

    public function test_index_returns_customer_name_and_equipment_count(): void
    {
        $customer = Customer::factory()->create(['name' => 'Green Tower Offices']);
        $location = Location::factory()->for($customer)->create();
        Equipment::factory()->for($customer)->for($location)->create();

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->getJson('/api/v1/locations');

        $response->assertOk();
        $response->assertJsonPath('data.0.customerName', 'Green Tower Offices');
        $response->assertJsonPath('data.0.equipmentCount', 1);
    }

    public function test_index_filters_by_customer_id(): void
    {
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        Location::factory()->for($customerA)->create();
        Location::factory()->for($customerB)->create();

        $response = $this->actingAs($this->userWithRole('Admin'))
            ->getJson("/api/v1/locations?customer_id={$customerA->id}");

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.customerId', $customerA->id);
    }

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/locations')->assertForbidden();
    }

    public function test_store_creates_a_location_for_an_existing_customer(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/locations', [
            'customer_id' => $customer->id,
            'name' => 'North Wing',
            'address' => '1 Main St',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.customerId', $customer->id);
    }

    public function test_store_rejects_a_nonexistent_customer(): void
    {
        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/locations', [
            'customer_id' => 999999,
            'name' => 'North Wing',
            'address' => '1 Main St',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('customer_id');
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/locations/{$location->id}")->assertForbidden();
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/locations/{$location->id}")->assertNoContent();

        $this->assertSoftDeleted($location);
    }
}
