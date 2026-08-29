<?php

namespace Tests\Feature\Api;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryItemsControllerTest extends TestCase
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
        InventoryItem::factory()->create();

        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/inventory-items')->assertOk();
    }

    public function test_show_computes_stock_status_from_levels(): void
    {
        $lowStock = InventoryItem::factory()->create(['available_stock' => 3, 'reserved' => 0, 'low_stock_threshold' => 5]);
        $outOfStock = InventoryItem::factory()->create(['available_stock' => 0, 'reserved' => 0, 'low_stock_threshold' => 5]);
        $inStock = InventoryItem::factory()->create(['available_stock' => 50, 'reserved' => 0, 'low_stock_threshold' => 5]);
        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)->getJson("/api/v1/inventory-items/{$lowStock->id}")->assertJsonPath('data.status', 'Low Stock');
        $this->actingAs($admin)->getJson("/api/v1/inventory-items/{$outOfStock->id}")->assertJsonPath('data.status', 'Out of Stock');
        $this->actingAs($admin)->getJson("/api/v1/inventory-items/{$inStock->id}")->assertJsonPath('data.status', 'In Stock');
    }

    public function test_store_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->postJson('/api/v1/inventory-items', [
            'part_name' => 'Test Part', 'sku' => 'TP-1', 'category' => 'Filters',
        ])->assertForbidden();
    }

    public function test_store_creates_an_item_for_a_dispatcher(): void
    {
        $response = $this->actingAs($this->userWithRole('Dispatcher'))->postJson('/api/v1/inventory-items', [
            'part_name' => 'Test Part', 'sku' => 'TP-1', 'category' => 'Filters',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('inventory_items', ['sku' => 'TP-1']);
    }

    public function test_store_rejects_a_duplicate_sku(): void
    {
        InventoryItem::factory()->create(['sku' => 'DUP-1']);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/inventory-items', [
            'part_name' => 'Test Part', 'sku' => 'DUP-1', 'category' => 'Filters',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('sku');
    }

    public function test_update_cannot_set_available_stock_directly(): void
    {
        $item = InventoryItem::factory()->create(['available_stock' => 10]);

        $response = $this->actingAs($this->userWithRole('Admin'))->putJson("/api/v1/inventory-items/{$item->id}", [
            'available_stock' => 999,
        ]);

        $response->assertOk();
        $this->assertSame(10, $item->fresh()->available_stock);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $item = InventoryItem::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/inventory-items/{$item->id}")->assertForbidden();
    }

    public function test_destroy_soft_deletes_for_admin(): void
    {
        $item = InventoryItem::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/inventory-items/{$item->id}")->assertNoContent();

        $this->assertSoftDeleted($item);
    }
}
