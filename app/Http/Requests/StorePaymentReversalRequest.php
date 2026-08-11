<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentReversalRequest extends FormRequest
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
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Payment $payment */
            $payment = $this->route('payment');

            if ($payment->status !== 'paid') {
                $validator->errors()->add('reason', 'Only a payment currently marked "paid" can be reversed.');

                return;
            }

            if ($payment->reversal !== null) {
                $validator->errors()->add('reason', 'This payment has already been reversed.');
            }
        });
    }
}
