<?php

namespace App\Http\Requests;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('tool'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'brand' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['Available', 'Assigned', 'Maintenance', 'Out of Service'])],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'last_inspection' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['status', 'assigned_to'])) {
                    return;
                }

                $this->validateAssignmentMatchesStatus($validator);
                $this->validateAssigneeIsATechnician($validator);
            },
        ];
    }

    private function validateAssignmentMatchesStatus(Validator $validator): void
    {
        /** @var Tool $tool */
        $tool = $this->route('tool');

        $status = $this->has('status') ? $this->input('status') : $tool->status;
        $hasAssignee = $this->has('assigned_to') ? $this->filled('assigned_to') : ! is_null($tool->assigned_to);

        if ($hasAssignee && $status !== 'Assigned') {
            $validator->errors()->add('status', 'A tool with an assigned technician must have the "Assigned" status.');
        }

        if (! $hasAssignee && $status === 'Assigned') {
            $validator->errors()->add('assigned_to', 'Assigning a tool requires a technician.');
        }
    }

    private function validateAssigneeIsATechnician(Validator $validator): void
    {
        if (! $this->filled('assigned_to')) {
            return;
        }

        $assignee = User::find($this->integer('assigned_to'));

        if ($assignee && ! $assignee->hasRole('Technician')) {
            $validator->errors()->add('assigned_to', 'Tools can only be assigned to a technician.');
        }
    }
}
