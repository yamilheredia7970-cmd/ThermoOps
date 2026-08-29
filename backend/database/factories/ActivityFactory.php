<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['WorkOrder', 'Equipment', 'Customer', 'System']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'actor_id' => User::factory(),
            'occurred_at' => now(),
        ];
    }
}
