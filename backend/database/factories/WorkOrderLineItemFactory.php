<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderLineItem>
 */
class WorkOrderLineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'type' => 'labor',
            'description' => fake()->sentence(3),
            'inventory_item_id' => null,
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 50, 300),
        ];
    }

    public function part(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'part',
            'inventory_item_id' => InventoryItem::factory(),
            'quantity' => fake()->numberBetween(1, 3),
        ]);
    }
}
