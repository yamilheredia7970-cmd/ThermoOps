<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('inventoryItem'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'part_name' => ['sometimes', 'string', 'max:255'],
            'sku' => ['sometimes', 'string', 'max:100', Rule::unique('inventory_items', 'sku')->ignore($this->route('inventoryItem'))],
            'category' => ['sometimes', 'string', 'max:255'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0'],
            'unit_cost' => ['sometimes', 'numeric', 'min:0'],
            // available_stock/reserved are intentionally not editable here:
            // they only move through the InventoryLedger so every change is
            // auditable via inventory_transactions. Restocking is a separate
            // endpoint (Phase 4).
        ];
    }
}
