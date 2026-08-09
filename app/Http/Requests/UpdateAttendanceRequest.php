<?php

namespace App\Http\Requests;

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
            'student_id.unique' => 'An attendance record already exists for this student, course, and date.',
        ];
    }
}
