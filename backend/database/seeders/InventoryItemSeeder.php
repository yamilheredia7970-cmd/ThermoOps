<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Mirrors the frontend's mockData.ts `mockInventory` fixture. reserved
     * starts at 0 for every item (rather than copying the mock's arbitrary
     * snapshot numbers) because reservations only mean something when they
     * are backed by real work_order_line_items + inventory_transactions
     * rows; WorkOrderSeeder creates a couple of those through the actual
     * InventoryLedger so the numbers stay internally consistent.
     */
    public function run(): void
    {
        InventoryItem::create(['part_name' => 'R410A Refrigerant (25lb)', 'sku' => 'REF-410A-25', 'category' => 'Refrigerants', 'available_stock' => 3, 'low_stock_threshold' => 5, 'unit_cost' => 180]);
        InventoryItem::create(['part_name' => 'Condenser Fan Motor 1/3 HP', 'sku' => 'MTR-CF-33', 'category' => 'Motors', 'available_stock' => 12, 'low_stock_threshold' => 4, 'unit_cost' => 95]);
        InventoryItem::create(['part_name' => 'Dual Run Capacitor 45/5 uF', 'sku' => 'CAP-455-440', 'category' => 'Electrical', 'available_stock' => 45, 'low_stock_threshold' => 10, 'unit_cost' => 22]);
        InventoryItem::create(['part_name' => 'Air Filter 20x25x1 MERV 11', 'sku' => 'FLT-20251-11', 'category' => 'Filters', 'available_stock' => 150, 'low_stock_threshold' => 50, 'unit_cost' => 8]);
        InventoryItem::create(['part_name' => 'Thermostatic Expansion Valve', 'sku' => 'TXV-3TON-R410', 'category' => 'Valves', 'available_stock' => 0, 'low_stock_threshold' => 3, 'unit_cost' => 65]);
    }
}
