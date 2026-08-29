<?php

namespace Database\Factories;

use App\Models\ServiceReport;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceReport>
 */
class ServiceReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory()->state(['status' => 'Completed']),
            'customer_id' => fn (array $attributes) => WorkOrder::find($attributes['work_order_id'])->customer_id,
            'location_id' => fn (array $attributes) => WorkOrder::find($attributes['work_order_id'])->location_id,
            'technician_id' => fn (array $attributes) => WorkOrder::find($attributes['work_order_id'])->technician_id,
            'type' => 'Maintenance',
            'status' => 'Pending Signature',
            'subtotal' => 100,
            'tax' => 0,
            'total' => 100,
        ];
    }
}
