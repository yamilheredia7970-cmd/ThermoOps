<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'since' => $this->since->toDateString(),
            'locationsCount' => $this->locations_count,
            'equipmentCount' => $this->equipment_count,
            // Wired to real work-order data once work orders exist (Phase 3).
            'activeWorkOrders' => 0,
            'status' => $this->status,
        ];
    }
}
