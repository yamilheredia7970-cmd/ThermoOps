<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
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
            'equipment_id' => null,
            'technician_id' => null,
            'created_by' => User::factory(),
            'service_type' => fake()->randomElement(['Maintenance', 'Repair', 'Installation', 'Inspection']),
            'status' => 'Scheduled',
            'priority' => 'Normal',
            'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
            'duration_hours' => fake()->randomElement([1, 1.5, 2, 2.5, 3, 4]),
            'description' => fake()->sentence(),
        ];
    }
}
