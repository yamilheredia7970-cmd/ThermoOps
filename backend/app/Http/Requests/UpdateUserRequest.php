<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isAdmin = $this->user()->hasRole('Admin');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'password' => ['sometimes', 'string', Password::defaults()],
            // Only an Admin may change account status or reassign a role.
            'status' => [$isAdmin ? 'sometimes' : 'prohibited', Rule::in(['active', 'inactive'])],
            'role' => [$isAdmin ? 'sometimes' : 'prohibited', Rule::in(['Admin', 'Dispatcher', 'Technician'])],
            'skills' => ['sometimes', 'array'],
            'skills.*' => ['string', 'max:100'],
        ];
    }
}
