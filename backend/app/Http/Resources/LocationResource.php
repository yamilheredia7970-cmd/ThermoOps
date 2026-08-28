<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'contactName' => $this->contact_name,
            'contactPhone' => $this->contact_phone,
            'equipmentCount' => $this->equipment_count,
            'lastVisit' => $this->last_visit_date?->toDateString(),
            'nextMaintenance' => $this->next_maintenance_date?->toDateString(),
        ];
    }
}
