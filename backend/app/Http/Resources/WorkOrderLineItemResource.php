<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderLineItemResource extends JsonResource
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
            'type' => $this->type,
            'description' => $this->description,
            'inventoryItemId' => $this->inventory_item_id,
            'quantity' => (float) $this->quantity,
            'unitPrice' => (float) $this->unit_price,
            'subtotal' => $this->subtotal(),
        ];
    }
}
