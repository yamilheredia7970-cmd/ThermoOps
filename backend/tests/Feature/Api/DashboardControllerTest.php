<?php

namespace Tests\Feature\Api;

use App\Models\InventoryItem;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
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

    private function technicianWithStatus(string $status): User
    {
        $user = $this->userWithRole('Technician');
        $user->technicianProfile()->create(['availability_status' => $status]);

        return $user;
    }

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $this->actingAs($this->userWithRole('Technician'))->getJson('/api/v1/dashboard')->assertForbidden();
    }

    public function test_index_counts_active_work_orders(): void
    {
        WorkOrder::factory()->create(['status' => 'Scheduled']);
        WorkOrder::factory()->create(['status' => 'In Progress']);
        WorkOrder::factory()->create(['status' => 'Completed']);

        $response = $this->actingAs($this->userWithRole('Admin'))->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.activeWorkOrders', 2);
    }

    public function test_index_summarizes_technician_availability(): void
    {
        $this->technicianWithStatus('Available');
        $this->technicianWithStatus('On Site');
        $this->technicianWithStatus('In Transit');
        $this->technicianWithStatus('Off Duty');

        $response = $this->actingAs($this->userWithRole('Dispatcher'))->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonPath('data.technicians.available', 1);
        $response->assertJsonPath('data.technicians.total', 4);
        $response->assertJsonPath('data.technicians.inField', 2);
    }

    public function test_index_sums_line_items_from_work_orders_completed_this_month(): void
    {
        $completedThisMonth = WorkOrder::factory()->create([
            'status' => 'Completed', 'completed_at' => now()->startOfMonth()->addDays(2),
        ]);
        $completedThisMonth->lineItems()->create(['type' => 'labor', 'description' => 'Labor', 'quantity' => 2, 'unit_price' => 100]);

        $completedLastMonth = WorkOrder::factory()->create([
            'status' => 'Completed', 'completed_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay(),
        ]);
        $completedLastMonth->lineItems()->create(['type' => 'labor', 'description' => 'Labor', 'quantity' => 1, 'unit_price' => 500]);

        $notYetCompleted = WorkOrder::factory()->create(['status' => 'In Progress']);
        $item = InventoryItem::factory()->create();
        $notYetCompleted->lineItems()->create([
            'type' => 'part', 'description' => 'Part', 'inventory_item_id' => $item->id, 'quantity' => 1, 'unit_price' => 1000,
        ]);

        $response = $this->actingAs($this->userWithRole('Admin'))->getJson('/api/v1/dashboard');

        $response->assertOk();
        // JSON doesn't distinguish 200 from 200.0, so the decoded value is an int.
        $response->assertJsonPath('data.monthlyRevenue', 200);
    }

    public function test_index_includes_todays_schedule_with_customer_and_technician_names(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create([
            'technician_id' => $technician->id, 'scheduled_at' => today()->setTime(9, 0),
        ]);
        WorkOrder::factory()->create(['scheduled_at' => now()->addDays(3)]);

        $response = $this->actingAs($this->userWithRole('Admin'))->getJson('/api/v1/dashboard');

        $response->assertOk();
        $response->assertJsonCount(1, 'data.todaysSchedule');
        $response->assertJsonPath('data.todaysSchedule.0.id', $workOrder->id);
        $response->assertJsonPath('data.todaysSchedule.0.technicianName', $technician->name);
    }
}
