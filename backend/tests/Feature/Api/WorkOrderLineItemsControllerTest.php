<?php

namespace Tests\Feature\Api;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\InventoryLedger;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkOrderLineItemsControllerTest extends TestCase
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

    public function test_store_creates_a_labor_line_item(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);

        $response = $this->actingAs($this->userWithRole('Dispatcher'))
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'labor', 'description' => 'Diagnostic labor', 'quantity' => 1.5, 'unit_price' => 95,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.subtotal', 142.5);
    }

    public function test_store_creates_a_part_line_item_and_reserves_stock(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $item = InventoryItem::factory()->create(['available_stock' => 10, 'reserved' => 0, 'unit_cost' => 50]);

        $response = $this->actingAs($this->userWithRole('Admin'))
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id, 'quantity' => 2,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('data.unitPrice', 50);
        $this->assertSame(2, $item->fresh()->reserved);
        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id, 'type' => 'reserve', 'quantity' => 2,
        ]);
    }

    public function test_store_rejects_a_part_quantity_exceeding_available_stock(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $item = InventoryItem::factory()->create(['available_stock' => 2, 'reserved' => 0]);

        $response = $this->actingAs($this->userWithRole('Admin'))
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id, 'quantity' => 5,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('quantity');
        $this->assertSame(0, $item->fresh()->reserved);
    }

    public function test_store_is_forbidden_once_the_work_order_is_completed(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Completed']);

        $this->actingAs($this->userWithRole('Admin'))
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'labor', 'description' => 'Extra labor', 'unit_price' => 50,
            ])->assertForbidden();
    }

    public function test_store_is_forbidden_for_a_technician_not_assigned_to_the_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $otherTechnician = $this->userWithRole('Technician');

        $this->actingAs($otherTechnician)
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'labor', 'description' => 'Extra labor', 'unit_price' => 50,
            ])->assertForbidden();
    }

    public function test_store_allows_the_assigned_technician(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['status' => 'In Progress', 'technician_id' => $technician->id]);

        $this->actingAs($technician)
            ->postJson("/api/v1/work-orders/{$workOrder->id}/line-items", [
                'type' => 'labor', 'description' => 'Diagnostic labor', 'unit_price' => 50,
            ])->assertCreated();
    }

    public function test_destroy_releases_reserved_stock(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $item = InventoryItem::factory()->create(['available_stock' => 10, 'reserved' => 0]);
        $lineItem = $workOrder->lineItems()->create([
            'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id,
            'quantity' => 3, 'unit_price' => $item->unit_cost,
        ]);
        app(InventoryLedger::class)->reserve($item, 3, $workOrder, null);

        $this->actingAs($this->userWithRole('Admin'))
            ->deleteJson("/api/v1/work-orders/{$workOrder->id}/line-items/{$lineItem->id}")
            ->assertNoContent();

        $this->assertSame(0, $item->fresh()->reserved);
        $this->assertDatabaseMissing('work_order_line_items', ['id' => $lineItem->id]);
    }
}
