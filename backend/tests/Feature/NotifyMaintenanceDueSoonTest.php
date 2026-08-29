<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MaintenancePlan;
use App\Models\User;
use App\Notifications\MaintenanceDueSoon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotifyMaintenanceDueSoonTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Dispatcher', 'Technician'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_notifies_staff_about_plans_due_in_seven_days(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $technician = User::factory()->create();
        $technician->assignRole('Technician');

        $customer = Customer::factory()->create();
        $duePlan = MaintenancePlan::factory()->for($customer)->create([
            'status' => 'Active',
            'next_service' => now()->addDays(7)->toDateString(),
        ]);
        MaintenancePlan::factory()->for($customer)->create([
            'status' => 'Active',
            'next_service' => now()->addDays(14)->toDateString(),
        ]);

        $this->artisan('app:notify-maintenance-due-soon')->assertSuccessful();

        Notification::assertSentTo(
            $admin,
            MaintenanceDueSoon::class,
            fn ($notification) => $notification->plan->is($duePlan)
        );
        Notification::assertNotSentTo($technician, MaintenanceDueSoon::class);
    }

    public function test_does_nothing_when_no_plans_are_due(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $this->artisan('app:notify-maintenance-due-soon')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
