<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
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
}
