<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Services\StudentChargeResolver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentAllocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Drop any allocation row the staff member left blank or zero before
     * validation runs, so they only have to fill in the charges they're
     * actually paying toward rather than every row in the grid.
     */
    protected function prepareForValidation(): void
    {
        $allocations = collect($this->input('allocations', []))
            ->filter(fn ($row) => is_array($row) && (float) ($row['amount'] ?? 0) > 0)
            ->values()
            ->all();

        $this->merge(['allocations' => $allocations]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,mobile_money'],
            'reference_number' => ['nullable', 'string', 'max:255', 'unique:payments,reference_number'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.type' => ['required', 'in:training,online_certificate,student_certificate,service'],
            'allocations.*.id' => ['required', 'integer'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('student_id') || $validator->errors()->has('allocations')) {
                return;
            }

            $student = Student::find($this->input('student_id'));

            if ($student === null) {
                return;
            }

            $allocatedTotal = 0.0;
            $usedPerCharge = [];

            foreach ($this->input('allocations', []) as $index => $row) {
                $type = $row['type'] ?? '';
                $id = (int) ($row['id'] ?? 0);
                $amount = (float) ($row['amount'] ?? 0);
                $charge = StudentChargeResolver::find($student, $type, $id);

                if ($charge === null) {
                    $validator->errors()->add("allocations.{$index}.id", 'This charge is no longer available for this student.');

                    continue;
                }

                $key = "{$type}:{$id}";
                $usedPerCharge[$key] = ($usedPerCharge[$key] ?? 0) + $amount;

                if ($usedPerCharge[$key] > $charge['balance'] + 0.001) {
                    $validator->errors()->add("allocations.{$index}.amount", "This exceeds the remaining balance for {$charge['label']}.");

                    continue;
                }

                $allocatedTotal += $amount;
            }

            $paymentAmount = round((float) $this->input('amount', 0), 2);

            if (round($allocatedTotal, 2) !== $paymentAmount) {
                $validator->errors()->add('amount', 'The allocated amounts (₦'.number_format($allocatedTotal, 2).') must add up to the payment amount.');
            }
        });
    }
}
