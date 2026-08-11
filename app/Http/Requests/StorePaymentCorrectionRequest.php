<?php

namespace App\Http\Requests;

use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentCorrectionRequest extends FormRequest
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
            'allocations' => ['required', 'array'],
            'allocations.*.id' => ['required', 'integer', 'exists:payment_allocations,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('allocations')) {
                return;
            }

            /** @var Payment $payment */
            $payment = $this->route('payment');

            $existingIds = $payment->allocations->pluck('id')->sort()->values()->all();
            $submittedIds = collect($this->input('allocations', []))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            if ($existingIds !== $submittedIds) {
                $validator->errors()->add('allocations', "A correction can only re-split this payment's existing charges, not add or remove one.");

                return;
            }

            $newTotal = round(collect($this->input('allocations', []))->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);
            $paymentAmount = round((float) $payment->amount, 2);

            if ($newTotal !== $paymentAmount) {
                $validator->errors()->add('allocations', 'The corrected split must still add up to the payment total of ₦'.number_format($paymentAmount, 2).'.');
            }
        });
    }
}
