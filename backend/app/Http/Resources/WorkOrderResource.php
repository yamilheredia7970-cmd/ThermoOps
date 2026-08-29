<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
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
            'locationId' => $this->location_id,
            'locationName' => $this->whenLoaded('location', fn () => $this->location->name),
            'equipmentId' => $this->equipment_id,
            'equipmentName' => $this->whenLoaded('equipment', fn () => $this->equipment ? "{$this->equipment->brand} {$this->equipment->type}" : null),
            'technicianId' => $this->technician_id,
            'technicianName' => $this->whenLoaded('technician', fn () => $this->technician?->name),
            'serviceType' => $this->service_type,
            'status' => $this->status,
            'priority' => $this->priority,
            'scheduledDate' => $this->scheduled_at->toDateString(),
            'scheduledTime' => $this->scheduled_at->format('H:i'),
            'durationHours' => (float) $this->duration_hours,
            'description' => $this->description,
            'lineItems' => WorkOrderLineItemResource::collection($this->whenLoaded('lineItems')),
            'lineItemsTotal' => $this->when(
                $this->relationLoaded('lineItems'),
                fn () => round($this->lineItems->sum(fn ($lineItem) => $lineItem->subtotal()), 2)
            ),
        ];
    }
}
