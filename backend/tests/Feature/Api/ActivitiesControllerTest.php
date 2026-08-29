<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivitiesControllerTest extends TestCase
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

    public function test_admin_sees_the_full_activity_feed(): void
    {
        $admin = $this->userWithRole('Admin');
        Activity::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/activities');

        $response->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_technician_without_a_subject_filter_is_forbidden(): void
    {
        $technician = $this->userWithRole('Technician');

        $this->actingAs($technician)
            ->getJson('/api/v1/activities')
            ->assertForbidden();
    }

    public function test_technician_only_sees_activity_for_their_own_work_orders(): void
    {
        $technician = $this->userWithRole('Technician');
        $otherTechnician = $this->userWithRole('Technician');

        $ownWorkOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        $othersWorkOrder = WorkOrder::factory()->create(['technician_id' => $otherTechnician->id]);

        Activity::factory()->create([
            'subject_type' => 'WorkOrder',
            'subject_id' => $ownWorkOrder->id,
        ]);
        Activity::factory()->create([
            'subject_type' => 'WorkOrder',
            'subject_id' => $othersWorkOrder->id,
        ]);

        $response = $this->actingAs($technician)->getJson('/api/v1/activities?subject_type=WorkOrder');

        $response->assertOk()->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.relatedId', (string) $ownWorkOrder->id);
    }

    public function test_staff_can_filter_activity_by_technician_id(): void
    {
        $admin = $this->userWithRole('Admin');
        $technician = $this->userWithRole('Technician');
        $otherTechnician = $this->userWithRole('Technician');

        $ownWorkOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        $othersWorkOrder = WorkOrder::factory()->create(['technician_id' => $otherTechnician->id]);

        Activity::factory()->create(['subject_type' => 'WorkOrder', 'subject_id' => $ownWorkOrder->id]);
        Activity::factory()->create(['subject_type' => 'WorkOrder', 'subject_id' => $othersWorkOrder->id]);

        $response = $this->actingAs($admin)->getJson("/api/v1/activities?technician_id={$technician->id}");

        $response->assertOk()->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.relatedId', (string) $ownWorkOrder->id);
    }

    public function test_technician_can_view_their_own_activity_by_technician_id(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        Activity::factory()->create(['subject_type' => 'WorkOrder', 'subject_id' => $workOrder->id]);

        $this->actingAs($technician)
            ->getJson("/api/v1/activities?technician_id={$technician->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_activities_are_ordered_most_recent_first(): void
    {
        $admin = $this->userWithRole('Admin');

        $older = Activity::factory()->create(['occurred_at' => now()->subDay()]);
        $newer = Activity::factory()->create(['occurred_at' => now()]);

        $response = $this->actingAs($admin)->getJson('/api/v1/activities');

        $response->assertOk();
        $this->assertSame((string) $newer->id, $response->json('data.0.id'));
        $this->assertSame((string) $older->id, $response->json('data.1.id'));
    }
}
