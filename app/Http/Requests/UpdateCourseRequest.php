<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
            'course_type' => ['required', 'in:manual,automatic,both'],
            'schedule' => ['required', 'in:weekday,weekend'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'duration_weeks' => ['required', 'integer', 'min:1'],
            'fee' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'instructors' => ['nullable', 'array'],
            'instructors.*' => ['integer', 'exists:instructors,id'],
            'students' => ['nullable', 'array'],
            'students.*' => ['integer', 'exists:students,id'],
        ];
    }
}
