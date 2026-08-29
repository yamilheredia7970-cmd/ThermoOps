<?php

namespace App\Http\Requests;

use App\Models\InventoryItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkOrderLineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageLineItems', $this->route('workOrder'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['labor', 'part', 'other'])],
            'description' => ['required', 'string', 'max:255'],
            'inventory_item_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'part'),
                Rule::prohibitedIf(fn () => $this->input('type') !== 'part'),
                'integer',
                'exists:inventory_items,id',
            ],
            // Parts are whole units (you can't reserve half a compressor);
            // labor/other lines may have a fractional quantity (e.g. hours).
            'quantity' => [
                'sometimes',
                Rule::when(fn () => $this->input('type') === 'part', ['integer', 'min:1'], ['numeric', 'min:0.01']),
            ],
            // Required unless it's a part line, whose price defaults to the
            // inventory item's unit cost when not explicitly overridden.
            'unit_price' => [Rule::requiredIf(fn () => $this->input('type') !== 'part'), 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->input('type') !== 'part' || $validator->errors()->hasAny(['inventory_item_id', 'quantity'])) {
                    return;
                }

                $item = InventoryItem::find($this->input('inventory_item_id'));
                $quantity = (float) $this->input('quantity', 1);
                $availableToPromise = $item->available_stock - $item->reserved;

                if ($item && $quantity > $availableToPromise) {
                    $validator->errors()->add('quantity', "Only {$availableToPromise} unit(s) of \"{$item->part_name}\" are available.");
                }
            },
        ];
    }
}
