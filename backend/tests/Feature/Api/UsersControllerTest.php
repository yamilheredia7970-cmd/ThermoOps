<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public static function nonAdminRoles(): array
    {
        return [
            'dispatcher' => ['Dispatcher'],
            'technician' => ['Technician'],
        ];
    }

    public function test_index_lists_users_for_admin(): void
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin())->getJson('/api/v1/users');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    #[DataProvider('nonAdminRoles')]
    public function test_index_is_forbidden_for_non_admin(string $role): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }

    public function test_store_creates_a_user_and_assigns_role(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/v1/users', [
            'name' => 'Dana Dispatcher',
            'email' => 'dana@thermoops.com',
            'password' => 'correct-horse-battery',
            'role' => 'Dispatcher',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.email', 'dana@thermoops.com');
        $response->assertJsonPath('data.roles', ['Dispatcher']);
        $this->assertDatabaseHas('users', ['email' => 'dana@thermoops.com']);
    }

    public function test_store_creates_technician_profile_when_role_is_technician(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/v1/users', [
            'name' => 'Terry Technician',
            'email' => 'terry@thermoops.com',
            'password' => 'correct-horse-battery',
            'role' => 'Technician',
            'skills' => ['Chillers', 'Controls'],
        ]);

        $response->assertCreated();
        $user = User::where('email', 'terry@thermoops.com')->firstOrFail();
        $this->assertSame(['Chillers', 'Controls'], $user->technicianProfile->skills);
    }

    #[DataProvider('nonAdminRoles')]
    public function test_store_is_forbidden_for_non_admin(string $role): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->postJson('/api/v1/users', [
            'name' => 'Nobody',
            'email' => 'nobody@thermoops.com',
            'password' => 'correct-horse-battery',
            'role' => 'Dispatcher',
        ])->assertForbidden();
    }

    public function test_store_requires_name_email_password_and_role(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/v1/users', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'role']);
    }

    public function test_store_rejects_a_duplicate_email(): void
    {
        $existing = User::factory()->create();

        $response = $this->actingAs($this->admin())->postJson('/api/v1/users', [
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'correct-horse-battery',
            'role' => 'Dispatcher',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_show_allows_admin_to_view_any_user(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin())->getJson("/api/v1/users/{$target->id}")->assertOk();
    }

    public function test_show_allows_a_user_to_view_their_own_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson("/api/v1/users/{$user->id}")->assertOk();
    }

    public function test_show_forbids_viewing_another_users_account(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->getJson("/api/v1/users/{$other->id}")->assertForbidden();
    }

    public function test_update_allows_admin_to_change_role_and_status(): void
    {
        $target = User::factory()->create();
        $target->assignRole('Dispatcher');

        $response = $this->actingAs($this->admin())->putJson("/api/v1/users/{$target->id}", [
            'status' => 'inactive',
            'role' => 'Technician',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', 'inactive');
        $response->assertJsonPath('data.roles', ['Technician']);
    }

    public function test_update_prohibits_a_non_admin_from_changing_role_or_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Technician');

        $response = $this->actingAs($user)->putJson("/api/v1/users/{$user->id}", [
            'status' => 'inactive',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('status');
    }

    public function test_update_allows_a_user_to_update_their_own_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_destroy_soft_deletes_the_user_for_admin(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->admin())->deleteJson("/api/v1/users/{$target->id}")->assertNoContent();

        $this->assertSoftDeleted($target);
    }

    public function test_destroy_prevents_an_admin_from_deleting_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->deleteJson("/api/v1/users/{$admin->id}")->assertForbidden();
        $this->assertNotSoftDeleted($admin);
    }

    #[DataProvider('nonAdminRoles')]
    public function test_destroy_is_forbidden_for_non_admin(string $role): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $target = User::factory()->create();

        $this->actingAs($user)->deleteJson("/api/v1/users/{$target->id}")->assertForbidden();
    }
}
