<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTicketRequest extends FormRequest
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
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
                Rule::unique('tickets')
                    ->where(fn ($query) => $query->where('course_id', $this->input('course_id'))->where('date', $this->input('date')))
                    ->ignore($this->route('ticket')),
            ],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'date' => ['required', 'date'],
            'vehicle' => ['nullable', 'string', 'max:255'],
            'lesson_number' => ['nullable', 'integer', 'min:1'],
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
            'student_id.unique' => 'A ticket has already been issued to this student for this course today.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('student_id') || $validator->errors()->has('course_id')) {
                return;
            }

            $enrollment = Enrollment::where('student_id', $this->input('student_id'))
                ->where('course_id', $this->input('course_id'))
                ->first();

            if (! $enrollment) {
                $validator->errors()->add('student_id', 'This student is not enrolled in the selected course.');

                return;
            }

            $enrollment->refreshStatus();

            if ($enrollment->status === 'locked') {
                $reason = $enrollment->locked_reason === 'training_period_expired'
                    ? 'the training period has expired'
                    : 'an overdue balance';

                $validator->errors()->add('student_id', "A ticket cannot be issued: this student is locked due to {$reason}.");
            }
        });
    }
}
