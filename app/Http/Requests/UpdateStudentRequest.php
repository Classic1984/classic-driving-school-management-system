<?php

namespace App\Http\Requests;

use App\Rules\ValidLocalGovernmentArea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('students', 'email')->ignore($this->route('student'))],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'mother_maiden_name' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'state_of_origin' => ['nullable', Rule::in(array_keys(config('nigeria.states')))],
            'local_government_area' => ['nullable', 'string', new ValidLocalGovernmentArea($this->input('state_of_origin'))],
            'occupation' => ['nullable', 'string', 'max:255'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_address' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:20'],
            'next_of_kin_email' => ['nullable', 'string', 'email', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50', Rule::unique('students', 'license_number')->ignore($this->route('student'))],
            'course_type' => ['required', 'in:manual,automatic,both'],
            'vehicle_class' => ['nullable', 'in:light,heavy'],
            'has_driving_experience' => ['nullable', 'boolean'],
            'wears_glasses' => ['nullable', 'boolean'],
            'referral_source' => ['nullable', 'in:flyer,referral,facebook,other'],
            'referral_source_other' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'enrollment_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:active,completed,withdrawn'],
        ];
    }
}
