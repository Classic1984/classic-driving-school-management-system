<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCorporateInvoiceSettingRequest extends FormRequest
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
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'invoice_prefix' => ['nullable', 'string', 'max:20', 'alpha_dash'],
            'quotation_prefix' => ['nullable', 'string', 'max:20', 'alpha_dash'],
            'receipt_prefix' => ['nullable', 'string', 'max:20', 'alpha_dash'],
            'programme_options' => ['nullable', 'string'],
            'duration_options' => ['nullable', 'string'],
            'driver_count_options' => ['nullable', 'string'],
            'service_options' => ['nullable', 'string'],
            'signature' => ['nullable', 'image', 'max:2048'],
            'remove_signature' => ['nullable', 'boolean'],
        ];
    }
}
