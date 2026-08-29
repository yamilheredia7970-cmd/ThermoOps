<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'part_name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'category' => fake()->randomElement(['Refrigerants', 'Motors', 'Electrical', 'Filters', 'Valves']),
            'available_stock' => fake()->numberBetween(10, 100),
            'reserved' => 0,
            'low_stock_threshold' => fake()->numberBetween(5, 20),
            'unit_cost' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
