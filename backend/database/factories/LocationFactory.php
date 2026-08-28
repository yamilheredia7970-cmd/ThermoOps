<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
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
            'name' => fake()->streetName().' Site',
            'address' => fake()->streetAddress(),
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->phoneNumber(),
            'last_visit_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'next_maintenance_date' => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }
}
