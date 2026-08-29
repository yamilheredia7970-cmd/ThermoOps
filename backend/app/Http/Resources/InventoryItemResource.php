<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
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
            'partName' => $this->part_name,
            'sku' => $this->sku,
            'category' => $this->category,
            'availableStock' => $this->available_stock,
            'reserved' => $this->reserved,
            'lowStockThreshold' => $this->low_stock_threshold,
            'status' => $this->stockStatus(),
        ];
    }
}
