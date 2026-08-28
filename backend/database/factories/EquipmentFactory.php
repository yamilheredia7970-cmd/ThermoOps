<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'customer_id' => fn (array $attributes) => Location::find($attributes['location_id'])->customer_id,
            'type' => fake()->randomElement(['Rooftop Unit', 'Split System', 'Chiller', 'VRV System']),
            'brand' => fake()->randomElement(['Daikin', 'Carrier', 'York', 'Mitsubishi', 'Trane']),
            'model' => fake()->bothify('??-####'),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'installation_date' => fake()->dateTimeBetween('-6 years', '-1 year'),
            'warranty_expiration' => fake()->dateTimeBetween('now', '+3 years'),
            'status' => 'Good',
        ];
    }
}
