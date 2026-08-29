<?php

namespace App\Models;

use Database\Factories\WorkOrderLineItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['work_order_id', 'type', 'description', 'inventory_item_id', 'quantity', 'unit_price'])]
class WorkOrderLineItem extends Model
{
    /** @use HasFactory<WorkOrderLineItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function subtotal(): float
    {
        return round((float) $this->quantity * (float) $this->unit_price, 2);
    }
}
