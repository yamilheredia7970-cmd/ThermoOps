<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderAssigned;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationsControllerTest extends TestCase
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

    public function test_index_lists_only_the_authenticated_users_notifications(): void
    {
        $technician = $this->userWithRole('Technician');
        $other = $this->userWithRole('Technician');

        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        $technician->notify(new WorkOrderAssigned($workOrder));
        $other->notify(new WorkOrderAssigned($workOrder));

        $response = $this->actingAs($technician)->getJson('/api/v1/notifications');

        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_unread_count_reflects_unread_notifications(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);

        $technician->notify(new WorkOrderAssigned($workOrder));
        $technician->notify(new WorkOrderAssigned($workOrder));

        $response = $this->actingAs($technician)->getJson('/api/v1/notifications/unread-count');

        $response->assertOk()->assertJson(['count' => 2]);
    }

    public function test_mark_as_read_marks_a_single_notification(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        $technician->notify(new WorkOrderAssigned($workOrder));
        $notificationId = $technician->notifications()->first()->id;

        $this->actingAs($technician)
            ->postJson("/api/v1/notifications/{$notificationId}/read")
            ->assertNoContent();

        $this->assertNotNull($technician->notifications()->first()->read_at);
    }

    public function test_mark_as_read_rejects_another_users_notification(): void
    {
        $technician = $this->userWithRole('Technician');
        $other = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $other->id]);
        $other->notify(new WorkOrderAssigned($workOrder));
        $notificationId = $other->notifications()->first()->id;

        $this->actingAs($technician)
            ->postJson("/api/v1/notifications/{$notificationId}/read")
            ->assertNotFound();
    }

    public function test_mark_all_as_read_clears_unread_count(): void
    {
        $technician = $this->userWithRole('Technician');
        $workOrder = WorkOrder::factory()->create(['technician_id' => $technician->id]);
        $technician->notify(new WorkOrderAssigned($workOrder));
        $technician->notify(new WorkOrderAssigned($workOrder));

        $this->actingAs($technician)
            ->postJson('/api/v1/notifications/read-all')
            ->assertNoContent();

        $this->actingAs($technician)
            ->getJson('/api/v1/notifications/unread-count')
            ->assertJson(['count' => 0]);
    }
}
