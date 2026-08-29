<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\MaintenancePlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMaintenancePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MaintenancePlan::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'plan_name' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'max:255'],
            'next_service' => ['required', 'date'],
            'status' => ['sometimes', Rule::in(['Active', 'Expired', 'Pending'])],
            'equipment_ids' => ['sometimes', 'array'],
            'equipment_ids.*' => ['integer', 'exists:equipment,id'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['customer_id', 'equipment_ids'])) {
                    return;
                }

                $this->validateEquipmentBelongsToCustomer($validator);
            },
        ];
    }

    private function validateEquipmentBelongsToCustomer(Validator $validator): void
    {
        $equipmentIds = $this->input('equipment_ids', []);

        if (empty($equipmentIds)) {
            return;
        }

        $foreignCount = Equipment::query()
            ->whereIn('id', $equipmentIds)
            ->where('customer_id', '!=', $this->integer('customer_id'))
            ->count();

        if ($foreignCount > 0) {
            $validator->errors()->add('equipment_ids', 'All equipment must belong to the selected customer.');
        }
    }
}
