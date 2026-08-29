<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'work_order_id' => null,
            'type' => 'restock',
            'quantity' => fake()->numberBetween(1, 20),
            'performed_by' => null,
            'occurred_at' => now(),
        ];
    }
}
