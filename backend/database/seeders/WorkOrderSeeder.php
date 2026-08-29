<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\InventoryLedger;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the shape of the frontend's mockWorkOrders fixture, built on
     * top of the customers/locations/equipment/technicians CustomerSeeder
     * and TechnicianSeeder already created. Two of the work orders get real
     * billing line items pushed through the InventoryLedger, so their
     * inventory reservations/consumption are backed by an actual
     * inventory_transactions trail instead of a hand-set snapshot number.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@thermoops.com')->firstOrFail();
        $carlos = User::where('email', 'cmartinez@thermoops.com')->firstOrFail();
        $david = User::where('email', 'dkim@thermoops.com')->firstOrFail();
        $marcus = User::where('email', 'mjohnson@thermoops.com')->firstOrFail();

        $greenTower = Customer::where('name', 'Green Tower Offices')->firstOrFail();
        $novaLogistics = Customer::where('name', 'Nova Logistics')->firstOrFail();
        $sunrisePlaza = Customer::where('name', 'Sunrise Retail Plaza')->firstOrFail();
        $andersonResidence = Customer::where('name', 'Anderson Residence')->firstOrFail();
        $techHub = Customer::where('name', 'TechHub Datacenter')->firstOrFail();

        $downtownBranch = Location::where('customer_id', $greenTower->id)->where('name', 'Downtown Branch')->firstOrFail();
        $headquarters = Location::where('customer_id', $greenTower->id)->where('name', 'Headquarters')->firstOrFail();
        $mainWarehouse = Location::where('customer_id', $novaLogistics->id)->firstOrFail();
        $unitA = Location::where('customer_id', $sunrisePlaza->id)->firstOrFail();
        $mainHouse = Location::where('customer_id', $andersonResidence->id)->firstOrFail();
        $serverRoomB = Location::where('customer_id', $techHub->id)->firstOrFail();

        $vrvSystem = Equipment::where('location_id', $downtownBranch->id)->firstOrFail();
        $chiller = Equipment::where('location_id', $mainWarehouse->id)->firstOrFail();
        $splitSystem = Equipment::where('location_id', $unitA->id)->firstOrFail();

        $inProgress = WorkOrder::create([
            'customer_id' => $greenTower->id, 'location_id' => $downtownBranch->id, 'equipment_id' => $vrvSystem->id,
            'technician_id' => $carlos->id, 'created_by' => $admin->id, 'service_type' => 'Maintenance',
            'status' => 'In Progress', 'priority' => 'Normal',
            'scheduled_at' => today()->setTime(9, 0), 'duration_hours' => 2.5,
            'description' => 'Quarterly preventative maintenance on VRV system. Check filters and refrigerant levels.',
        ]);

        WorkOrder::create([
            'customer_id' => $novaLogistics->id, 'location_id' => $mainWarehouse->id, 'equipment_id' => $chiller->id,
            'technician_id' => $david->id, 'created_by' => $admin->id, 'service_type' => 'Repair',
            'status' => 'Scheduled', 'priority' => 'Urgent',
            'scheduled_at' => today()->setTime(13, 0), 'duration_hours' => 3,
            'description' => 'Chiller throwing high-pressure fault codes. Immediate inspection required.',
        ]);

        WorkOrder::create([
            'customer_id' => $andersonResidence->id, 'location_id' => $mainHouse->id, 'equipment_id' => null,
            'technician_id' => null, 'created_by' => $admin->id, 'service_type' => 'Installation',
            'status' => 'Scheduled', 'priority' => 'Normal',
            'scheduled_at' => today()->setTime(8, 0), 'duration_hours' => 4,
            'description' => 'Install new smart thermostat and configure zones.',
        ]);

        $completed = WorkOrder::create([
            'customer_id' => $sunrisePlaza->id, 'location_id' => $unitA->id, 'equipment_id' => $splitSystem->id,
            'technician_id' => $marcus->id, 'created_by' => $admin->id, 'service_type' => 'Inspection',
            'status' => 'Completed', 'priority' => 'Low',
            'scheduled_at' => today()->setTime(14, 0), 'duration_hours' => 1.5, 'completed_at' => today()->setTime(15, 20),
            'description' => 'Annual safety inspection and duct cleaning.',
        ]);

        WorkOrder::create([
            'customer_id' => $techHub->id, 'location_id' => $serverRoomB->id, 'equipment_id' => null,
            'technician_id' => null, 'created_by' => $admin->id, 'service_type' => 'Repair',
            'status' => 'On Hold', 'priority' => 'High',
            'scheduled_at' => today()->setTime(10, 0), 'duration_hours' => 2,
            'description' => 'Awaiting specialized compressor part from supplier.',
        ]);

        WorkOrder::create([
            'customer_id' => $greenTower->id, 'location_id' => $headquarters->id, 'equipment_id' => null,
            'technician_id' => $carlos->id, 'created_by' => $admin->id, 'service_type' => 'Repair',
            'status' => 'Scheduled', 'priority' => 'High',
            'scheduled_at' => today()->setTime(16, 0), 'duration_hours' => 1.5,
            'description' => 'Fixing rattling noise reported by tenant.',
        ]);

        // Billing example: labor + a consumed part on the completed job.
        $completed->lineItems()->create([
            'type' => 'labor', 'description' => 'Inspection labor', 'quantity' => 1.5, 'unit_price' => 95,
        ]);
        $filter = InventoryItem::where('sku', 'FLT-20251-11')->firstOrFail();
        $completed->lineItems()->create([
            'type' => 'part', 'description' => $filter->part_name, 'inventory_item_id' => $filter->id,
            'quantity' => 2, 'unit_price' => $filter->unit_cost,
        ]);
        $ledger = app(InventoryLedger::class);
        $ledger->consume($filter, 2, $completed, $marcus);

        // Billing example: a reserved (not yet consumed) part on the in-progress job.
        $refrigerant = InventoryItem::where('sku', 'REF-410A-25')->firstOrFail();
        $inProgress->lineItems()->create([
            'type' => 'part', 'description' => $refrigerant->part_name, 'inventory_item_id' => $refrigerant->id,
            'quantity' => 2, 'unit_price' => $refrigerant->unit_cost,
        ]);
        $ledger->reserve($refrigerant, 2, $inProgress, $carlos);
    }
}
