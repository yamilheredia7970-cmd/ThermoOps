<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the frontend's src/data/mockData.ts customers/locations/
     * equipment fixtures. The mock's decorative locationsCount/equipmentCount
     * numbers don't actually match its own location/equipment arrays, so
     * this seeder builds a relationally consistent set instead of copying
     * those inconsistent counts verbatim; the API computes real counts.
     */
    public function run(): void
    {
        $greenTower = Customer::create(['name' => 'Green Tower Offices', 'type' => 'Commercial', 'since' => '2021-03-15']);
        $novaLogistics = Customer::create(['name' => 'Nova Logistics', 'type' => 'Industrial', 'since' => '2019-11-02']);
        $sunrisePlaza = Customer::create(['name' => 'Sunrise Retail Plaza', 'type' => 'Commercial', 'since' => '2022-06-20']);
        $andersonResidence = Customer::create(['name' => 'Anderson Residence', 'type' => 'Residential', 'since' => '2023-01-10']);
        $techHub = Customer::create(['name' => 'TechHub Datacenter', 'type' => 'Commercial', 'since' => '2020-08-05']);

        $headquarters = Location::create([
            'customer_id' => $greenTower->id,
            'name' => 'Headquarters',
            'address' => '1420 Business Pkwy, Suite 100',
            'contact_name' => 'Sarah Jenkins',
            'contact_phone' => '(555) 123-4567',
            'last_visit_date' => '2026-08-15',
            'next_maintenance_date' => '2026-09-15',
        ]);
        $downtownBranch = Location::create([
            'customer_id' => $greenTower->id,
            'name' => 'Downtown Branch',
            'address' => '800 Commerce St, Floor 6',
            'contact_name' => 'Michael Chang',
            'contact_phone' => '(555) 987-6543',
            'last_visit_date' => '2026-08-02',
            'next_maintenance_date' => '2026-10-01',
        ]);
        $mainWarehouse = Location::create([
            'customer_id' => $novaLogistics->id,
            'name' => 'Main Warehouse',
            'address' => '500 Industrial Blvd',
            'contact_name' => 'Robert Vance',
            'contact_phone' => '(555) 444-3333',
            'last_visit_date' => '2026-07-20',
            'next_maintenance_date' => '2026-09-20',
        ]);
        $unitA = Location::create([
            'customer_id' => $sunrisePlaza->id,
            'name' => 'Unit A',
            'address' => '210 Sunrise Plaza',
            'next_maintenance_date' => '2027-02-10',
        ]);
        Location::create([
            'customer_id' => $andersonResidence->id,
            'name' => 'Main House',
            'address' => '48 Anderson Ln',
        ]);
        Location::create([
            'customer_id' => $techHub->id,
            'name' => 'Server Room B',
            'address' => '900 Datacenter Dr',
        ]);

        Equipment::create([
            'customer_id' => $greenTower->id,
            'location_id' => $downtownBranch->id,
            'type' => 'VRV System',
            'brand' => 'Daikin',
            'model' => 'VRV IV',
            'serial_number' => 'DXR-492821',
            'installation_date' => '2022-03-10',
            'warranty_expiration' => '2027-06-01',
            'status' => 'Attention',
        ]);
        Equipment::create([
            'customer_id' => $greenTower->id,
            'location_id' => $headquarters->id,
            'type' => 'Rooftop Unit',
            'brand' => 'Carrier',
            'model' => 'WeatherMaker 48TC',
            'serial_number' => 'CAR-99381A',
            'installation_date' => '2021-05-15',
            'warranty_expiration' => '2026-05-15',
            'status' => 'Good',
        ]);
        Equipment::create([
            'customer_id' => $novaLogistics->id,
            'location_id' => $mainWarehouse->id,
            'type' => 'Chiller',
            'brand' => 'York',
            'model' => 'YVAA',
            'serial_number' => 'YRK-8822001',
            'installation_date' => '2019-12-05',
            'warranty_expiration' => '2024-12-05',
            'status' => 'Critical',
        ]);
        Equipment::create([
            'customer_id' => $sunrisePlaza->id,
            'location_id' => $unitA->id,
            'type' => 'Split System',
            'brand' => 'Mitsubishi',
            'model' => 'P-Series',
            'serial_number' => 'MIT-55611',
            'installation_date' => '2023-02-20',
            'warranty_expiration' => '2028-02-20',
            'status' => 'Good',
        ]);
    }
}
