<?php

namespace App\Http\Requests;

use App\Models\ServiceReport;
use App\Models\WorkOrder;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! $this->user()->can('create', ServiceReport::class)) {
            return false;
        }

        if ($this->user()->hasAnyRole(['Admin', 'Dispatcher'])) {
            return true;
        }

        // A technician may only generate a report for their own job.
        $workOrder = WorkOrder::find($this->input('work_order_id'));

        return (bool) $workOrder && $workOrder->technician_id === $this->user()->id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'work_order_id' => [
                'required', 'integer', 'exists:work_orders,id',
                Rule::unique('service_reports', 'work_order_id'),
            ],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('work_order_id')) {
                    return;
                }

                $workOrder = WorkOrder::find($this->input('work_order_id'));

                if ($workOrder && $workOrder->status !== 'Completed') {
                    $validator->errors()->add('work_order_id', 'A service report can only be generated for a completed work order.');
                }
            },
        ];
    }
}
