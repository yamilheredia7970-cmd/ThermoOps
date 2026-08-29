<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the frontend's mockData.ts `mockTools` fixture.
     */
    public function run(): void
    {
        $carlos = User::where('email', 'cmartinez@thermoops.com')->firstOrFail();
        $david = User::where('email', 'dkim@thermoops.com')->firstOrFail();

        Tool::create(['name' => 'Fieldpiece VP67 Vacuum Pump', 'brand' => 'Fieldpiece', 'category' => 'Evacuation', 'status' => 'Assigned', 'assigned_to' => $carlos->id, 'last_inspection' => '2026-07-15']);
        Tool::create(['name' => 'Appion G5Twin Recovery Machine', 'brand' => 'Appion', 'category' => 'Recovery', 'status' => 'Available', 'last_inspection' => '2026-08-01']);
        Tool::create(['name' => 'Testo 550s Digital Manifold', 'brand' => 'Testo', 'category' => 'Measurement', 'status' => 'Assigned', 'assigned_to' => $david->id, 'last_inspection' => '2026-06-20']);
        Tool::create(['name' => 'Fluke 116 HVAC Multimeter', 'brand' => 'Fluke', 'category' => 'Electrical', 'status' => 'Maintenance', 'last_inspection' => '2026-08-25']);
        Tool::create(['name' => 'Navac NP4DP Vacuum Pump', 'brand' => 'Navac', 'category' => 'Evacuation', 'status' => 'Available', 'last_inspection' => '2026-07-30']);
    }
}
