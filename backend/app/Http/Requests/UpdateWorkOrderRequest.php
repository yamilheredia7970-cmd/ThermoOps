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

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workOrder'));
    }

    /**
     * A technician updating their own work order may only change its status
     * and description; dispatch decisions (who, where, when) stay with
     * Admin/Dispatcher.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isManager = $this->user()->hasAnyRole(['Admin', 'Dispatcher']);
        $managerOnly = $isManager ? 'sometimes' : 'prohibited';

        return [
            'customer_id' => [$managerOnly, 'integer', 'exists:customers,id'],
            'location_id' => [$managerOnly, 'integer', 'exists:locations,id'],
            'equipment_id' => [$managerOnly, 'nullable', 'integer', 'exists:equipment,id'],
            'technician_id' => [$managerOnly, 'nullable', 'integer', 'exists:users,id'],
            'service_type' => [$managerOnly, Rule::in(['Maintenance', 'Repair', 'Installation', 'Inspection'])],
            'status' => ['sometimes', Rule::in(['Scheduled', 'In Progress', 'On Hold', 'Completed', 'Cancelled'])],
            'priority' => [$managerOnly, Rule::in(['Low', 'Normal', 'High', 'Urgent'])],
            'scheduled_at' => [$managerOnly, 'date'],
            'duration_hours' => [$managerOnly, 'numeric', 'min:0.25', 'max:24'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var WorkOrder $workOrder */
                $workOrder = $this->route('workOrder');

                $this->validateLocationBelongsToCustomer($validator, $workOrder);
                $this->validateEquipmentBelongsToLocation($validator, $workOrder);
                $this->validateTechnicianIsATechnician($validator);
                $this->validateNoSchedulingConflict($validator, $workOrder);
            },
        ];
    }

    private function effectiveCustomerId(WorkOrder $workOrder): int
    {
        return $this->has('customer_id') ? $this->integer('customer_id') : $workOrder->customer_id;
    }

    private function effectiveLocationId(WorkOrder $workOrder): int
    {
        return $this->has('location_id') ? $this->integer('location_id') : $workOrder->location_id;
    }

    private function validateLocationBelongsToCustomer(Validator $validator, WorkOrder $workOrder): void
    {
        if (! $this->has('location_id') && ! $this->has('customer_id')) {
            return;
        }

        $location = Location::find($this->effectiveLocationId($workOrder));

        if ($location && $location->customer_id !== $this->effectiveCustomerId($workOrder)) {
            $validator->errors()->add('location_id', 'The selected location does not belong to the selected customer.');
        }
    }

    private function validateEquipmentBelongsToLocation(Validator $validator, WorkOrder $workOrder): void
    {
        $equipmentId = $this->has('equipment_id') ? $this->input('equipment_id') : $workOrder->equipment_id;

        if (! $equipmentId) {
            return;
        }

        $equipment = Equipment::find($equipmentId);

        if ($equipment && $equipment->location_id !== $this->effectiveLocationId($workOrder)) {
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

    private function validateNoSchedulingConflict(Validator $validator, WorkOrder $workOrder): void
    {
        $technicianId = $this->has('technician_id') ? $this->input('technician_id') : $workOrder->technician_id;

        if (! $technicianId) {
            return;
        }

        $status = $this->input('status', $workOrder->status);

        if (in_array($status, ['Completed', 'Cancelled'], true)) {
            return;
        }

        $start = $this->has('scheduled_at') ? Carbon::parse($this->input('scheduled_at')) : $workOrder->scheduled_at;
        $durationHours = $this->has('duration_hours') ? $this->float('duration_hours') : (float) $workOrder->duration_hours;
        $end = $start->clone()->addMinutes((int) round($durationHours * 60));

        if (WorkOrder::hasSchedulingConflict((int) $technicianId, $start, $end, $workOrder->id)) {
            $validator->errors()->add('technician_id', 'This technician already has a work order scheduled during this time window.');
        }
    }
}
