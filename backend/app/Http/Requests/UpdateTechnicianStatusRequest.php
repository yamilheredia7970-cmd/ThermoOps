<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTechnicianStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateTechnicianStatus', $this->route('technician'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['On Site', 'Available', 'Off Duty', 'In Transit'])],
        ];
    }
}
