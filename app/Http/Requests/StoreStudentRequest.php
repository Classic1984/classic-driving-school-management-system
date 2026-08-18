<?php

namespace App\Http\Requests;

use App\Rules\ValidLocalGovernmentArea;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The discount presets this request's user is allowed to apply.
     * Everyone can pick from the standard tier - EnrollmentService holds a
     * non-Director's choice as a pending DiscountRequest rather than
     * applying it outright, so this isn't a trust boundary the way it is
     * for the higher tier and "custom" (a hand-entered percentage or fixed
     * amount), which stay Director-only and apply immediately.
     *
     * @return list<string>
     */
    protected function allowedDiscountChoices(): array
    {
        $choices = array_map('strval', config('discounts.standard_presets'));

        if ($this->user()?->isDirector()) {
            $choices = [
                ...$choices,
                ...array_map('strval', config('discounts.director_presets')),
                'custom',
            ];
        }

        return $choices;
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'mother_maiden_name' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'in:male,female'],
            'state_of_origin' => ['nullable', Rule::in(array_keys(config('nigeria.states')))],
            'local_government_area' => ['nullable', 'string', new ValidLocalGovernmentArea($this->input('state_of_origin'))],
            'occupation' => ['nullable', 'in:student,business,other'],
            'next_of_kin_name' => ['nullable', 'string', 'max:255'],
            'next_of_kin_address' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:20'],
            'next_of_kin_email' => ['nullable', 'string', 'email', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50', 'unique:students,license_number'],
            'course_type' => ['required', 'in:manual,automatic,both'],
            'vehicle_class' => ['nullable', 'in:light,heavy'],
            'has_driving_experience' => ['nullable', 'boolean'],
            'wears_glasses' => ['nullable', 'boolean'],
            'referral_source' => ['nullable', 'in:flyer,referral,facebook,other'],
            'referral_source_other' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'enrollment_date' => ['required', 'date', 'date_equals:today'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'starts_double_period' => ['nullable', 'boolean'],
            'amount_paid' => ['nullable', 'numeric', 'min:0.01'],
            'payment_method' => ['required_with:amount_paid', 'nullable', 'in:cash,card,bank_transfer,mobile_money'],
            'discount_choice' => ['nullable', Rule::in($this->allowedDiscountChoices())],
            'custom_discount_percentage' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'custom_discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'discount_reason' => ['nullable', Rule::in(array_keys(config('discounts.reasons')))],
            'discount_reason_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'discount_choice.in' => 'You are not authorized to apply that discount.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $choice = $this->input('discount_choice');

            if ($choice === 'custom') {
                $hasPercentage = $this->filled('custom_discount_percentage');
                $hasAmount = $this->filled('custom_discount_amount');

                if ($hasPercentage === $hasAmount) {
                    $validator->errors()->add('custom_discount_percentage', 'Enter either a custom percentage or a fixed amount, not both.');
                }
            }

            if ($choice && ! $this->filled('discount_reason')) {
                $validator->errors()->add('discount_reason', 'A reason is required whenever a discount is applied.');
            }

            if ($this->input('discount_reason') === 'other' && ! $this->filled('discount_reason_note')) {
                $validator->errors()->add('discount_reason_note', 'Please specify the reason.');
            }
        });
    }
}
