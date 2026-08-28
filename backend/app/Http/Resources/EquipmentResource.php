<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
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
            'locationId' => $this->location_id,
            'type' => $this->type,
            'brand' => $this->brand,
            'model' => $this->model,
            'serialNumber' => $this->serial_number,
            'installationDate' => $this->installation_date?->toDateString(),
            'warrantyExpiration' => $this->warranty_expiration?->toDateString(),
            'status' => $this->status,
            'locationName' => $this->whenLoaded('location', fn () => $this->location->name),
        ];
    }
}
