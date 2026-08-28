<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('equipment'));
    }

    /**
     * customer_id is intentionally not accepted here: it is derived
     * server-side from location_id so it can never drift from it.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['sometimes', 'integer', 'exists:locations,id'],
            'type' => ['sometimes', 'string', 'max:255'],
            'brand' => ['sometimes', 'string', 'max:255'],
            'model' => ['sometimes', 'string', 'max:255'],
            'serial_number' => ['sometimes', 'string', 'max:255', Rule::unique('equipment', 'serial_number')->ignore($this->route('equipment'))],
            'installation_date' => ['sometimes', 'nullable', 'date'],
            'warranty_expiration' => ['sometimes', 'nullable', 'date', 'after_or_equal:installation_date'],
            'status' => ['sometimes', Rule::in(['Good', 'Attention', 'Critical'])],
        ];
    }
}
