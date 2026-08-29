<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\MaintenancePlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMaintenancePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('maintenancePlan'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'integer', 'exists:customers,id'],
            'plan_name' => ['sometimes', 'string', 'max:255'],
            'frequency' => ['sometimes', 'string', 'max:255'],
            'next_service' => ['sometimes', 'date'],
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
                if ($validator->errors()->hasAny(['customer_id', 'equipment_ids']) || ! $this->has('equipment_ids')) {
                    return;
                }

                $this->validateEquipmentBelongsToCustomer($validator);
            },
        ];
    }

    private function validateEquipmentBelongsToCustomer(Validator $validator): void
    {
        /** @var MaintenancePlan $plan */
        $plan = $this->route('maintenancePlan');
        $customerId = $this->has('customer_id') ? $this->integer('customer_id') : $plan->customer_id;
        $equipmentIds = $this->input('equipment_ids', []);

        if (empty($equipmentIds)) {
            return;
        }

        $foreignCount = Equipment::query()
            ->whereIn('id', $equipmentIds)
            ->where('customer_id', '!=', $customerId)
            ->count();

        if ($foreignCount > 0) {
            $validator->errors()->add('equipment_ids', 'All equipment must belong to the selected customer.');
        }
    }
}
