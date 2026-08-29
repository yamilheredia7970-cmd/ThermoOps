<?php

namespace Tests\Feature\Api;

use App\Events\ActivityLogged;
use App\Events\InventoryLowStock;
use App\Events\WorkOrderSaved;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderAssigned;
use App\Services\InventoryLedger;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkOrdersControllerTest extends TestCase
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

    private function technician(): User
    {
        return $this->userWithRole('Technician');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(Location $location, array $overrides = []): array
    {
        return array_merge([
            'customer_id' => $location->customer_id,
            'location_id' => $location->id,
            'service_type' => 'Maintenance',
            'scheduled_at' => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'duration_hours' => 2,
        ], $overrides);
    }

    public static function staffRoles(): array
    {
        return ['admin' => ['Admin'], 'dispatcher' => ['Dispatcher']];
    }

    public function test_index_scopes_results_to_a_technicians_own_work_orders(): void
    {
        $technician = $this->technician();
        $own = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        WorkOrder::factory()->create();

        $response = $this->actingAs($technician)->getJson('/api/v1/work-orders');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $own->id);
    }

    #[DataProvider('staffRoles')]
    public function test_index_shows_every_work_order_to_staff(string $role): void
    {
        WorkOrder::factory()->count(2)->create();

        $response = $this->actingAs($this->userWithRole($role))->getJson('/api/v1/work-orders');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    #[DataProvider('staffRoles')]
    public function test_store_creates_a_work_order(string $role): void
    {
        $location = Location::factory()->create();

        $response = $this->actingAs($this->userWithRole($role))
            ->postJson('/api/v1/work-orders', $this->payloadFor($location));

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'Scheduled');
        $this->assertDatabaseHas('work_orders', ['location_id' => $location->id]);
    }

    public function test_store_is_forbidden_for_a_technician(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->technician())
            ->postJson('/api/v1/work-orders', $this->payloadFor($location))
            ->assertForbidden();
    }

    public function test_store_rejects_a_location_that_does_not_belong_to_the_customer(): void
    {
        $location = Location::factory()->create();
        $otherCustomer = Customer::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor(
            $location,
            ['customer_id' => $otherCustomer->id]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('location_id');
    }

    public function test_store_rejects_equipment_that_does_not_belong_to_the_location(): void
    {
        $location = Location::factory()->create();
        $foreignEquipment = Equipment::factory()->create();

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor(
            $location,
            ['equipment_id' => $foreignEquipment->id]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('equipment_id');
    }

    public function test_store_rejects_a_technician_id_that_is_not_a_technician(): void
    {
        $location = Location::factory()->create();
        $dispatcher = $this->userWithRole('Dispatcher');

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor(
            $location,
            ['technician_id' => $dispatcher->id]
        ));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('technician_id');
    }

    public function test_store_rejects_an_overlapping_schedule_for_the_same_technician(): void
    {
        $technician = $this->technician();
        $location = Location::factory()->create();
        $start = now()->addDay()->setTime(9, 0);
        WorkOrder::factory()->create([
            'location_id' => $location->id, 'customer_id' => $location->customer_id,
            'technician_id' => $technician->id, 'status' => 'Scheduled',
            'scheduled_at' => $start, 'duration_hours' => 2,
        ]);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor($location, [
            'technician_id' => $technician->id,
            'scheduled_at' => $start->clone()->addHour()->toDateTimeString(), // overlaps 09:00-11:00
            'duration_hours' => 2,
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('technician_id');
    }

    public function test_store_allows_a_non_overlapping_schedule_for_the_same_technician(): void
    {
        $technician = $this->technician();
        $location = Location::factory()->create();
        $start = now()->addDay()->setTime(9, 0);
        WorkOrder::factory()->create([
            'location_id' => $location->id, 'customer_id' => $location->customer_id,
            'technician_id' => $technician->id, 'status' => 'Scheduled',
            'scheduled_at' => $start, 'duration_hours' => 2,
        ]);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor($location, [
            'technician_id' => $technician->id,
            'scheduled_at' => $start->clone()->addHours(2)->toDateTimeString(), // starts exactly when the first ends
            'duration_hours' => 1,
        ]));

        $response->assertCreated();
    }

    public function test_store_ignores_cancelled_work_orders_when_checking_conflicts(): void
    {
        $technician = $this->technician();
        $location = Location::factory()->create();
        $start = now()->addDay()->setTime(9, 0);
        WorkOrder::factory()->create([
            'location_id' => $location->id, 'customer_id' => $location->customer_id,
            'technician_id' => $technician->id, 'status' => 'Cancelled',
            'scheduled_at' => $start, 'duration_hours' => 2,
        ]);

        $response = $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor($location, [
            'technician_id' => $technician->id,
            'scheduled_at' => $start->toDateTimeString(),
            'duration_hours' => 2,
        ]));

        $response->assertCreated();
    }

    public function test_update_by_technician_cannot_change_dispatch_fields(): void
    {
        $technician = $this->technician();
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id, 'priority' => 'Normal']);

        $response = $this->actingAs($technician)->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'priority' => 'Urgent',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('priority');
    }

    public function test_update_by_technician_can_change_their_own_work_order_status(): void
    {
        $technician = $this->technician();
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id, 'status' => 'Scheduled']);

        $response = $this->actingAs($technician)->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'In Progress',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'In Progress');
    }

    public function test_update_to_completed_consumes_reserved_part_line_items(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'In Progress']);
        $item = InventoryItem::factory()->create(['available_stock' => 20, 'reserved' => 0]);
        $lineItem = $workOrder->lineItems()->create([
            'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id,
            'quantity' => 3, 'unit_price' => $item->unit_cost,
        ]);
        app(InventoryLedger::class)->reserve($item, 3, $workOrder, null);

        $response = $this->actingAs($this->userWithRole('Admin'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'Completed',
        ]);

        $response->assertOk();
        $item->refresh();
        $this->assertSame(17, $item->available_stock);
        $this->assertSame(0, $item->reserved);
        $this->assertDatabaseHas('inventory_transactions', [
            'inventory_item_id' => $item->id, 'work_order_id' => $workOrder->id, 'type' => 'consume', 'quantity' => 3,
        ]);
        $this->assertNotNull($workOrder->fresh()->completed_at);
    }

    public function test_update_to_cancelled_releases_reserved_part_line_items(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $item = InventoryItem::factory()->create(['available_stock' => 20, 'reserved' => 0]);
        $workOrder->lineItems()->create([
            'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id,
            'quantity' => 3, 'unit_price' => $item->unit_cost,
        ]);
        app(InventoryLedger::class)->reserve($item, 3, $workOrder, null);

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'Cancelled',
        ]);

        $response->assertOk();
        $item->refresh();
        $this->assertSame(20, $item->available_stock);
        $this->assertSame(0, $item->reserved);
    }

    public function test_destroy_is_forbidden_for_a_dispatcher(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $this->actingAs($this->userWithRole('Dispatcher'))->deleteJson("/api/v1/work-orders/{$workOrder->id}")->assertForbidden();
    }

    public function test_destroy_releases_reserved_parts_and_soft_deletes_for_admin(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $item = InventoryItem::factory()->create(['available_stock' => 20, 'reserved' => 0]);
        $workOrder->lineItems()->create([
            'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id,
            'quantity' => 2, 'unit_price' => $item->unit_cost,
        ]);
        app(InventoryLedger::class)->reserve($item, 2, $workOrder, null);

        $this->actingAs($this->userWithRole('Admin'))->deleteJson("/api/v1/work-orders/{$workOrder->id}")->assertNoContent();

        $this->assertSoftDeleted($workOrder);
        $this->assertSame(0, $item->fresh()->reserved);
    }

    public function test_update_to_completed_broadcasts_low_stock_when_consumption_crosses_the_threshold(): void
    {
        Event::fake([InventoryLowStock::class]);
        $workOrder = WorkOrder::factory()->create(['status' => 'In Progress']);
        $item = InventoryItem::factory()->create(['available_stock' => 5, 'reserved' => 0, 'low_stock_threshold' => 3]);
        $workOrder->lineItems()->create([
            'type' => 'part', 'description' => $item->part_name, 'inventory_item_id' => $item->id,
            'quantity' => 3, 'unit_price' => $item->unit_cost,
        ]);
        app(InventoryLedger::class)->reserve($item, 3, $workOrder, null);

        $this->actingAs($this->userWithRole('Admin'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'Completed',
        ]);

        // 5 - 3 = 2, at or below the threshold of 3.
        Event::assertDispatched(InventoryLowStock::class, fn ($event) => $event->item->is($item));
    }

    public function test_store_broadcasts_work_order_saved(): void
    {
        Event::fake([WorkOrderSaved::class]);
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor($location));

        Event::assertDispatched(WorkOrderSaved::class);
    }

    public function test_update_broadcasts_work_order_saved(): void
    {
        Event::fake([WorkOrderSaved::class]);
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);

        $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'In Progress',
        ]);

        Event::assertDispatched(WorkOrderSaved::class, fn ($event) => $event->workOrder->is($workOrder));
    }

    public function test_store_logs_an_activity_and_notifies_the_assigned_technician(): void
    {
        Event::fake([ActivityLogged::class]);
        Notification::fake();
        $location = Location::factory()->create();
        $technician = $this->technician();

        $this->actingAs($this->userWithRole('Admin'))->postJson(
            '/api/v1/work-orders',
            $this->payloadFor($location, ['technician_id' => $technician->id])
        )->assertCreated();

        $this->assertDatabaseHas('activities', ['title' => 'New Work Order Dispatched']);
        Event::assertDispatched(ActivityLogged::class);
        Notification::assertSentTo($technician, WorkOrderAssigned::class);
    }

    public function test_store_without_a_technician_does_not_send_an_assignment_notification(): void
    {
        Notification::fake();
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('Admin'))->postJson('/api/v1/work-orders', $this->payloadFor($location))
            ->assertCreated();

        Notification::assertNothingSent();
    }

    public function test_update_logs_an_activity_when_the_status_changes(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled']);
        $actor = $this->userWithRole('Dispatcher');

        $this->actingAs($actor)->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'status' => 'In Progress',
        ])->assertOk();

        $this->assertDatabaseHas('activities', [
            'title' => 'Work Order Started',
            'subject_type' => 'WorkOrder',
            'subject_id' => $workOrder->id,
        ]);
    }

    public function test_update_logs_a_reassignment_and_notifies_the_new_technician(): void
    {
        Notification::fake();
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled', 'technician_id' => null]);
        $technician = $this->technician();

        $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'technician_id' => $technician->id,
        ])->assertOk();

        $this->assertDatabaseHas('activities', ['title' => 'Work Order Reassigned']);
        Notification::assertSentTo($technician, WorkOrderAssigned::class);
    }

    public function test_update_without_a_status_or_technician_change_logs_nothing(): void
    {
        $workOrder = WorkOrder::factory()->create(['status' => 'Scheduled', 'priority' => 'Normal']);

        $this->actingAs($this->userWithRole('Dispatcher'))->putJson("/api/v1/work-orders/{$workOrder->id}", [
            'priority' => 'High',
        ])->assertOk();

        $this->assertSame(0, Activity::count());
    }
}
