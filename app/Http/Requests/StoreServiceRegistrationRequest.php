<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRegistrationRequest extends FormRequest
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
            'mode' => ['required', 'in:existing,walk_in'],
            'student_id' => ['required_if:mode,existing', 'nullable', 'exists:students,id'],
            'name' => ['required_if:mode,walk_in', 'nullable', 'string', 'max:255'],
            'email' => ['required_if:mode,walk_in', 'nullable', 'string', 'email', 'max:255', 'unique:students,email'],
            'phone' => ['required_if:mode,walk_in', 'nullable', 'string', 'max:20'],
            'date_of_birth' => ['required_if:mode,walk_in', 'nullable', 'date', 'before:today'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,mobile_money'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
