<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttendanceRequest extends FormRequest
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
                Rule::unique('attendances')->where(fn ($query) => $query->where('course_id', $this->input('course_id'))->where('date', $this->input('date'))),
            ],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'instructor_id' => ['nullable', 'integer', 'exists:instructors,id'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'in:present,absent,late,excused'],
            'type' => ['nullable', 'in:practical,classroom'],
            'duration' => ['nullable', 'integer', 'in:1,2,3'],
            'notes' => ['nullable', 'string'],
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
            'student_id.unique' => $this->duplicateMessage(),
        ];
    }

    /**
     * A friendly, student-named message when the duplicate is for today
     * (the common case - logging the same student's training twice in one
     * sitting), falling back to a generic message for a backdated entry.
     */
    protected function duplicateMessage(): string
    {
        $student = Student::find($this->input('student_id'));

        if ($student && $this->input('date') === now()->toDateString()) {
            return "{$student->name} has already logged training today.";
        }

        return 'An attendance record already exists for this student, course, and date.';
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

                $validator->errors()->add('student_id', "Training cannot be logged: this student is locked due to {$reason}.");
            }
        });
    }
}
