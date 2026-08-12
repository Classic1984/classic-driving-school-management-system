<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends FormRequest
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
            'course_id' => [
                'required',
                'integer',
                'exists:courses,id',
                Rule::unique('course_student', 'course_id')->where('student_id', $this->route('student')->id),
            ],
            'starts_double_period' => ['nullable', 'boolean'],
            'amount_paid' => ['nullable', 'numeric', 'min:0.01'],
            'payment_method' => ['required_with:amount_paid', 'nullable', 'in:cash,card,bank_transfer,mobile_money'],
            'discount_choice' => ['nullable', Rule::in([
                ...array_map('strval', config('discounts.secretary_presets')),
                ...array_map('strval', config('discounts.director_presets')),
                'custom',
            ])],
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
            'course_id.unique' => 'This student is already enrolled in that course.',
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
