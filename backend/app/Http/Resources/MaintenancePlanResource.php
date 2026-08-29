<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenancePlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customer_id,
            'customerName' => $this->whenLoaded('customer', fn () => $this->customer->name),
            'planName' => $this->plan_name,
            'equipmentCount' => $this->equipment_count,
            'frequency' => $this->frequency,
            'nextService' => $this->next_service->toDateString(),
            'status' => $this->status,
        ];
    }
}
