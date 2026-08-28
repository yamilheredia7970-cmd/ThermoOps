<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TechnicianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the frontend's src/data/mockData.ts `mockTechnicians` fixture
     * so the seeded API responses line up with the existing UI screenshots.
     */
    public function run(): void
    {
        $technicians = [
            ['name' => 'Carlos Martinez', 'email' => 'cmartinez@thermoops.com', 'skills' => ['Commercial HVAC', 'VRF Systems', 'Chillers'], 'availability_status' => 'On Site', 'rating' => 4.9, 'completion_rate' => 98],
            ['name' => "Sarah O'Connor", 'email' => 'soconnor@thermoops.com', 'skills' => ['Residential', 'Heat Pumps', 'Ductwork'], 'availability_status' => 'Available', 'rating' => 4.7, 'completion_rate' => 95],
            ['name' => 'David Kim', 'email' => 'dkim@thermoops.com', 'skills' => ['Industrial', 'Chillers', 'Controls'], 'availability_status' => 'In Transit', 'rating' => 4.8, 'completion_rate' => 96],
            ['name' => 'Marcus Johnson', 'email' => 'mjohnson@thermoops.com', 'skills' => ['Commercial HVAC', 'Rooftop Units', 'Preventative'], 'availability_status' => 'On Site', 'rating' => 4.6, 'completion_rate' => 92],
            ['name' => 'Elena Rostova', 'email' => 'erostova@thermoops.com', 'skills' => ['Diagnostics', 'Electrical', 'VRF Systems'], 'availability_status' => 'Available', 'rating' => 4.9, 'completion_rate' => 99],
        ];

        foreach ($technicians as $technician) {
            $user = User::factory()->create([
                'name' => $technician['name'],
                'email' => $technician['email'],
                'password' => bcrypt('demo1234'),
            ]);

            $user->assignRole('Technician');

            $user->technicianProfile()->create([
                'skills' => $technician['skills'],
                'availability_status' => $technician['availability_status'],
                'rating' => $technician['rating'],
                'completion_rate' => $technician['completion_rate'],
            ]);
        }
    }
}
