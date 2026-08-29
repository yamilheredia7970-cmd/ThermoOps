<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use App\Models\Location;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WorkOrder::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
            'service_type' => ['required', Rule::in(['Maintenance', 'Repair', 'Installation', 'Inspection'])],
            'status' => ['sometimes', Rule::in(['Scheduled', 'In Progress', 'On Hold', 'Completed', 'Cancelled'])],
            'priority' => ['sometimes', Rule::in(['Low', 'Normal', 'High', 'Urgent'])],
            'scheduled_at' => ['required', 'date'],
            'duration_hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['location_id', 'customer_id', 'equipment_id', 'technician_id', 'scheduled_at', 'duration_hours'])) {
                    return;
                }

                $this->validateLocationBelongsToCustomer($validator);
                $this->validateEquipmentBelongsToLocation($validator);
                $this->validateTechnicianIsATechnician($validator);
                $this->validateNoSchedulingConflict($validator);
            },
        ];
    }

    private function validateLocationBelongsToCustomer(Validator $validator): void
    {
        $location = Location::find($this->integer('location_id'));

        if ($location && $location->customer_id !== $this->integer('customer_id')) {
            $validator->errors()->add('location_id', 'The selected location does not belong to the selected customer.');
        }
    }

    private function validateEquipmentBelongsToLocation(Validator $validator): void
    {
        if (! $this->filled('equipment_id')) {
            return;
        }

        $equipment = Equipment::find($this->integer('equipment_id'));

        if ($equipment && $equipment->location_id !== $this->integer('location_id')) {
            $validator->errors()->add('equipment_id', 'The selected equipment does not belong to the selected location.');
        }
    }

    private function validateTechnicianIsATechnician(Validator $validator): void
    {
        if (! $this->filled('technician_id')) {
            return;
        }

        $technician = User::find($this->integer('technician_id'));

        if ($technician && ! $technician->hasRole('Technician')) {
            $validator->errors()->add('technician_id', 'The selected user is not a technician.');
        }
    }

    private function validateNoSchedulingConflict(Validator $validator): void
    {
        if (! $this->filled('technician_id')) {
            return;
        }

        if (in_array($this->input('status', 'Scheduled'), ['Completed', 'Cancelled'], true)) {
            return;
        }

        $start = Carbon::parse($this->input('scheduled_at'));
        $end = $start->clone()->addMinutes((int) round($this->float('duration_hours') * 60));

        if (WorkOrder::hasSchedulingConflict($this->integer('technician_id'), $start, $end)) {
            $validator->errors()->add('technician_id', 'This technician already has a work order scheduled during this time window.');
        }
    }
}
