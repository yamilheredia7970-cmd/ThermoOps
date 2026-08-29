<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\MaintenancePlan;
use Illuminate\Database\Seeder;

class MaintenancePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the frontend's mockData.ts `mockMaintenancePlans` fixture.
     * Each plan is attached to all of that customer's actual seeded
     * equipment, so equipmentCount is a real, computed number rather than
     * the mock's unrelated decorative figure.
     */
    public function run(): void
    {
        $novaLogistics = Customer::where('name', 'Nova Logistics')->firstOrFail();
        $greenTower = Customer::where('name', 'Green Tower Offices')->firstOrFail();
        $sunrisePlaza = Customer::where('name', 'Sunrise Retail Plaza')->firstOrFail();

        $industrial = MaintenancePlan::create(['customer_id' => $novaLogistics->id, 'plan_name' => 'Industrial Annual Agreement', 'frequency' => 'Every 6 months', 'next_service' => '2026-09-15', 'status' => 'Active']);
        $industrial->equipment()->sync($novaLogistics->equipment()->pluck('id'));

        $premium = MaintenancePlan::create(['customer_id' => $greenTower->id, 'plan_name' => 'Premium Commercial Care', 'frequency' => 'Quarterly', 'next_service' => today()->toDateString(), 'status' => 'Active']);
        $premium->equipment()->sync($greenTower->equipment()->pluck('id'));

        $basic = MaintenancePlan::create(['customer_id' => $sunrisePlaza->id, 'plan_name' => 'Basic Preventative', 'frequency' => 'Annual', 'next_service' => '2027-02-10', 'status' => 'Pending']);
        $basic->equipment()->sync($sunrisePlaza->equipment()->pluck('id'));
    }
}
