<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'brand' => fake()->company(),
            'category' => fake()->randomElement(['Evacuation', 'Recovery', 'Measurement', 'Electrical']),
            'status' => 'Available',
            'assigned_to' => null,
            'last_inspection' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
