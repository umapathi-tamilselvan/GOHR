<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeProfileRequest extends FormRequest
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
        return [
            'employee_id' => 'required|exists:employees,id',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required|string|max:255',
            'certifications.*.issuing_organization' => 'nullable|string|max:255',
            'certifications.*.issue_date' => 'nullable|date',
            'certifications.*.expiry_date' => 'nullable|date|after:certifications.*.issue_date',
            'work_experience' => 'nullable|array',
            'work_experience.*.company' => 'required|string|max:255',
            'work_experience.*.position' => 'required|string|max:255',
            'work_experience.*.start_date' => 'required|date',
            'work_experience.*.end_date' => 'nullable|date|after:work_experience.*.start_date',
            'work_experience.*.description' => 'nullable|string|max:1000',
            'education' => 'nullable|array',
            'education.*.institution' => 'required|string|max:255',
            'education.*.degree' => 'required|string|max:255',
            'education.*.field_of_study' => 'nullable|string|max:255',
            'education.*.start_date' => 'required|date',
            'education.*.end_date' => 'nullable|date|after:education.*.start_date',
            'education.*.grade' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'date_of_birth.before' => 'Date of birth cannot be in the future.',
            'gender.in' => 'Invalid gender selection.',
            'marital_status.in' => 'Invalid marital status selection.',
            'phone.max' => 'Phone number is too long.',
            'emergency_contact_phone.max' => 'Emergency contact phone number is too long.',
            'skills.array' => 'Skills must be an array.',
            'skills.*.string' => 'Each skill must be a string.',
            'certifications.array' => 'Certifications must be an array.',
            'certifications.*.name.required' => 'Certification name is required.',
            'certifications.*.issue_date.date' => 'Certification issue date must be a valid date.',
            'certifications.*.expiry_date.after' => 'Certification expiry date must be after issue date.',
            'work_experience.array' => 'Work experience must be an array.',
            'work_experience.*.company.required' => 'Company name is required.',
            'work_experience.*.position.required' => 'Position is required.',
            'work_experience.*.start_date.required' => 'Start date is required.',
            'work_experience.*.end_date.after' => 'End date must be after start date.',
            'education.array' => 'Education must be an array.',
            'education.*.institution.required' => 'Institution name is required.',
            'education.*.degree.required' => 'Degree is required.',
            'education.*.start_date.required' => 'Start date is required.',
            'education.*.end_date.after' => 'End date must be after start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'employee',
            'date_of_birth' => 'date of birth',
            'gender' => 'gender',
            'marital_status' => 'marital status',
            'nationality' => 'nationality',
            'address' => 'address',
            'phone' => 'phone number',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'emergency_contact_relationship' => 'emergency contact relationship',
            'skills' => 'skills',
            'certifications' => 'certifications',
            'work_experience' => 'work experience',
            'education' => 'education',
        ];
    }
} 