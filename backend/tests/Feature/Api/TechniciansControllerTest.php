<?php

namespace Tests\Feature\Api;

use App\Events\TechnicianStatusChanged;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TechniciansControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function technician(array $profile = []): User
    {
        $user = User::factory()->create();
        $user->assignRole('Technician');
        $user->technicianProfile()->create([
            'skills' => ['Chillers'],
            'availability_status' => 'Available',
            'rating' => 4.5,
            'completion_rate' => 90,
            ...$profile,
        ]);

        return $user;
    }

    public static function staffRoles(): array
    {
        return [
            'admin' => ['Admin'],
            'dispatcher' => ['Dispatcher'],
        ];
    }

    #[DataProvider('staffRoles')]
    public function test_index_lists_technicians_for_staff_roles(string $role): void
    {
        $this->technician();
        $this->technician();
        $staff = User::factory()->create();
        $staff->assignRole($role);

        $response = $this->actingAs($staff)->getJson('/api/v1/technicians');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_index_is_forbidden_for_a_technician(): void
    {
        $technician = $this->technician();

        $this->actingAs($technician)->getJson('/api/v1/technicians')->assertForbidden();
    }

    #[DataProvider('staffRoles')]
    public function test_show_allows_staff_to_view_any_technician(string $role): void
    {
        $technician = $this->technician();
        $staff = User::factory()->create();
        $staff->assignRole($role);

        $this->actingAs($staff)->getJson("/api/v1/technicians/{$technician->id}")->assertOk();
    }

    public function test_show_allows_a_technician_to_view_their_own_profile(): void
    {
        $technician = $this->technician();

        $this->actingAs($technician)->getJson("/api/v1/technicians/{$technician->id}")->assertOk();
    }

    public function test_show_forbids_a_technician_from_viewing_a_colleagues_profile(): void
    {
        $technician = $this->technician();
        $colleague = $this->technician();

        $this->actingAs($technician)->getJson("/api/v1/technicians/{$colleague->id}")->assertForbidden();
    }

    public function test_show_returns_the_technician_resource_shape(): void
    {
        $technician = $this->technician([
            'skills' => ['Chillers', 'Controls'],
            'availability_status' => 'On Site',
            'rating' => 4.75,
            'completion_rate' => 87,
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->getJson("/api/v1/technicians/{$technician->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $technician->id);
        $response->assertJsonPath('data.skills', ['Chillers', 'Controls']);
        $response->assertJsonPath('data.status', 'On Site');
        $response->assertJsonPath('data.rating', 4.75);
        $response->assertJsonPath('data.completionRate', 87);
        $response->assertJsonPath('data.currentJobId', null);
        $response->assertJsonPath('data.jobsToday', 0);
    }

    public function test_show_computes_work_stats_from_real_work_orders(): void
    {
        $technician = $this->technician();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $inProgress = WorkOrder::factory()->create([
            'technician_id' => $technician->id, 'status' => 'In Progress',
            'scheduled_at' => today()->setTime(9, 0), 'duration_hours' => 2,
        ]);
        WorkOrder::factory()->create([
            'technician_id' => $technician->id, 'status' => 'Scheduled',
            'scheduled_at' => today()->setTime(14, 0), 'duration_hours' => 1.5,
        ]);
        // Outside this week: should not count toward hoursThisWeek.
        WorkOrder::factory()->create([
            'technician_id' => $technician->id, 'status' => 'Completed',
            'scheduled_at' => now()->subWeeks(2), 'duration_hours' => 10,
        ]);

        $response = $this->actingAs($admin)->getJson("/api/v1/technicians/{$technician->id}");

        $response->assertOk();
        $response->assertJsonPath('data.currentJobId', $inProgress->id);
        $response->assertJsonPath('data.jobsToday', 2);
        $response->assertJsonPath('data.hoursThisWeek', 3.5);
    }

    public function test_update_status_changes_availability_and_broadcasts(): void
    {
        Event::fake([TechnicianStatusChanged::class]);
        $technician = $this->technician(['availability_status' => 'Available']);

        $response = $this->actingAs($technician)->patchJson("/api/v1/technicians/{$technician->id}/status", [
            'status' => 'On Site',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'On Site');
        $this->assertSame('On Site', $technician->technicianProfile->fresh()->availability_status);
        Event::assertDispatched(TechnicianStatusChanged::class, fn ($event) => $event->technician->is($technician) && $event->status === 'On Site');
    }

    public function test_update_status_allows_staff_to_change_another_technicians_status(): void
    {
        $technician = $this->technician();
        $dispatcher = User::factory()->create();
        $dispatcher->assignRole('Dispatcher');

        $this->actingAs($dispatcher)->patchJson("/api/v1/technicians/{$technician->id}/status", [
            'status' => 'Off Duty',
        ])->assertOk();
    }

    public function test_update_status_is_forbidden_for_another_technician(): void
    {
        $technician = $this->technician();
        $colleague = $this->technician();

        $this->actingAs($colleague)->patchJson("/api/v1/technicians/{$technician->id}/status", [
            'status' => 'Off Duty',
        ])->assertForbidden();
    }

    public function test_update_status_rejects_an_invalid_value(): void
    {
        $technician = $this->technician();

        $response = $this->actingAs($technician)->patchJson("/api/v1/technicians/{$technician->id}/status", [
            'status' => 'On The Moon',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('status');
    }
}
