<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\MaintenancePlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenancePlan>
 */
class MaintenancePlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'plan_name' => fake()->randomElement(['Basic Preventative', 'Premium Commercial Care', 'Industrial Annual Agreement']),
            'frequency' => fake()->randomElement(['Quarterly', 'Every 6 months', 'Annual']),
            'next_service' => fake()->dateTimeBetween('now', '+6 months'),
            'status' => 'Active',
        ];
    }
}
