<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCertificateRequest extends FormRequest
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
                Rule::unique('certificates')
                    ->where(fn ($query) => $query->where('course_id', $this->input('course_id')))
                    ->ignore($this->route('certificate')),
            ],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'issue_date' => ['required', 'date'],
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
            'student_id.unique' => 'A certificate has already been issued to this student for this course.',
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

            if ($enrollment->status !== 'completed') {
                $validator->errors()->add('student_id', 'A certificate cannot be issued until this course is marked as completed for this student.');
            }
        });
    }
}
