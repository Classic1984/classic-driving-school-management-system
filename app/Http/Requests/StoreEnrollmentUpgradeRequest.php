<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Services\EnrollmentService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentUpgradeRequest extends FormRequest
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
        $enrollment = $this->route('enrollment');
        $eligibleCourseIds = $enrollment->canUpgrade() ? $enrollment->eligibleUpgradeCourses()->pluck('id') : collect();

        return [
            'course_id' => ['required', 'integer', Rule::in($eligibleCourseIds)],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'in:cash,card,bank_transfer,mobile_money'],
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
            'course_id.in' => 'That programme is not a valid upgrade for this enrollment.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $amountPaid = (float) $this->input('amount_paid', 0);

            if ($amountPaid > 0 && ! $this->filled('payment_method')) {
                $validator->errors()->add('payment_method', 'The payment method field is required.');
            }

            if ($validator->errors()->has('course_id') || $amountPaid <= 0) {
                return;
            }

            $enrollment = $this->route('enrollment');
            $newCourse = Course::find($this->input('course_id'));

            if ($newCourse === null) {
                return;
            }

            $upgradeCost = app(EnrollmentService::class)->upgradeCost($enrollment, $newCourse);

            if ($amountPaid > $upgradeCost + 0.001) {
                $validator->errors()->add('amount_paid', 'This exceeds the upgrade balance of ₦'.number_format($upgradeCost, 2).'.');
            }
        });
    }
}
