<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@thermoops.com',
            'password' => bcrypt('demo1234'),
        ]);
        $admin->assignRole('Admin');

        $this->call(TechnicianSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(InventoryItemSeeder::class);
        $this->call(WorkOrderSeeder::class);
        $this->call(ToolSeeder::class);
        $this->call(MaintenancePlanSeeder::class);
    }
}
