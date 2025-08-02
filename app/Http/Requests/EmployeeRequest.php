<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('employee');
        $userId = $employeeId ? $employeeId->user_id : null;

        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees')->ignore($employeeId),
            ],
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'hire_date' => 'required|date|before_or_equal:today',
            'termination_date' => 'nullable|date|after:hire_date',
            'status' => 'required|in:active,on_leave,terminated,probation',
            'salary' => 'nullable|numeric|min:0|max:9999999.99',
            'manager_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($userId) {
                    if ($value == $userId) {
                        $fail('An employee cannot be their own manager.');
                    }
                },
            ],
            'organization_id' => 'required|exists:organizations,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user for this employee.',
            'user_id.exists' => 'The selected user does not exist.',
            'user_id.unique' => 'This user is already an employee.',
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This Employee ID is already taken.',
            'department.required' => 'Department is required.',
            'position.required' => 'Position is required.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.before_or_equal' => 'Hire date cannot be in the future.',
            'termination_date.after' => 'Termination date must be after hire date.',
            'status.required' => 'Employee status is required.',
            'status.in' => 'Invalid employee status.',
            'salary.numeric' => 'Salary must be a valid number.',
            'salary.min' => 'Salary cannot be negative.',
            'manager_id.exists' => 'The selected manager does not exist.',
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'The selected organization does not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'employee_id' => 'employee ID',
            'department' => 'department',
            'position' => 'position',
            'hire_date' => 'hire date',
            'termination_date' => 'termination date',
            'status' => 'status',
            'salary' => 'salary',
            'manager_id' => 'manager',
            'organization_id' => 'organization',
        ];
    }
} 