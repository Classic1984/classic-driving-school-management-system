<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInstructorAttendanceRequest extends FormRequest
{
    /**
     * The 'instructor' middleware already guarantees the authenticated
     * user is an instructor - the course-ownership check below is what
     * actually keeps them from logging attendance outside their own
     * courses.
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
        $courseIds = $this->user()->instructor->courses->pluck('id')->all();

        return [
            'student_id' => [
                'required',
                'integer',
                'exists:students,id',
                Rule::unique('attendances')->where(fn ($query) => $query->where('course_id', $this->input('course_id'))->whereDate('date', today())),
            ],
            'course_id' => ['required', 'integer', Rule::in($courseIds)],
            'status' => ['required', 'in:present,absent,late,excused'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.unique' => $this->duplicateMessage(),
            'course_id.in' => 'You are not assigned to teach that course.',
        ];
    }

    protected function duplicateMessage(): string
    {
        $student = Student::find($this->input('student_id'));

        return $student
            ? "{$student->name} has already logged training today."
            : 'An attendance record already exists for this student, course, and date.';
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
