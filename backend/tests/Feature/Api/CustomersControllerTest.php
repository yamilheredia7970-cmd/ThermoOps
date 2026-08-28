<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomersControllerTest extends TestCase
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

    public static function managerRoles(): array
    {
        return [
            'admin' => ['Admin'],
            'dispatcher' => ['Dispatcher'],
        ];
    }

    #[DataProvider('managerRoles')]
    public function test_index_lists_customers_with_computed_counts(string $role): void
    {
        $customer = Customer::factory()->create();
        $location = Location::factory()->for($customer)->create();
        Equipment::factory()->for($customer)->for($location)->count(2)->create();

        $response = $this->actingAs($this->userWithRole($role))->getJson('/api/v1/customers');

        $response->assertOk();
        $response->assertJsonPath('data.0.locationsCount', 1);
        $response->assertJsonPath('data.0.equipmentCount', 2);
    }

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/customers')->assertForbidden();
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/customers')->assertUnauthorized();
    }

    #[DataProvider('managerRoles')]
    public function test_store_creates_a_customer(string $role): void
    {
        $response = $this->actingAs($this->userWithRole($role))->postJson('/api/v1/customers', [
            'name' => 'Acme HVAC Client',
            'type' => 'Commercial',
            'since' => '2024-01-15',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'Acme HVAC Client');
        $response->assertJsonPath('data.status', 'Active');
        $this->assertDatabaseHas('customers', ['name' => 'Acme HVAC Client']);
    }

    public function test_store_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->postJson('/api/v1/customers', [
            'name' => 'Acme HVAC Client',
            'type' => 'Commercial',
            'since' => '2024-01-15',
        ])->assertForbidden();
    }

    public function test_store_requires_name_type_and_since(): void
    {
        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/customers', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'type', 'since']);
    }

    public function test_store_rejects_an_invalid_type(): void
    {
        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/customers', [
            'name' => 'Acme HVAC Client',
            'type' => 'Government',
            'since' => '2024-01-15',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('type');
    }

    public function test_update_changes_customer_fields(): void
    {
        $customer = Customer::factory()->create(['status' => 'Active']);

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/customers/{$customer->id}", [
            'status' => 'Inactive',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'Inactive');
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/customers/{$customer->id}")->assertNoContent();

        $this->assertSoftDeleted($customer);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/customers/{$customer->id}")->assertForbidden();
        $this->assertNotSoftDeleted($customer);
    }
}
