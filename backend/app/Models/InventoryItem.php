<?php

namespace App\Models;

use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['part_name', 'sku', 'category', 'available_stock', 'reserved', 'low_stock_threshold', 'unit_cost'])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * 'In Stock' / 'Low Stock' / 'Out of Stock', derived from stock levels
     * rather than stored, so it can never drift from available_stock.
     */
    public function stockStatus(): string
    {
        if ($this->available_stock <= 0) {
            return 'Out of Stock';
        }

        if ($this->available_stock <= $this->low_stock_threshold) {
            return 'Low Stock';
        }

        return 'In Stock';
    }
}
