<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
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
                Rule::unique('attendances')
                    ->where(fn ($query) => $query->where('course_id', $this->input('course_id'))->where('date', $this->input('date')))
                    ->ignore($this->route('attendance')),
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
     * A friendly, student-named message when the duplicate is for today,
     * falling back to a generic message for a backdated entry.
     */
    protected function duplicateMessage(): string
    {
        $student = Student::find($this->input('student_id'));

        if ($student && $this->input('date') === now()->toDateString()) {
            return "{$student->name} has already logged training today.";
        }

        return 'An attendance record already exists for this student, course, and date.';
    }
}
