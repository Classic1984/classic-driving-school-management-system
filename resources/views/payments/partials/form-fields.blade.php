@php($payment = $payment ?? null)

<div>
    <x-input-label for="student_id" :value="__('Student')" />
    <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a student') }}</option>
        @foreach ($students as $availableStudent)
            <option value="{{ $availableStudent->id }}" @selected((int) old('student_id', $payment?->student_id) === $availableStudent->id)>{{ $availableStudent->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('student_id')" />
</div>

<div>
    <x-input-label for="course_id" :value="__('Course')" />
    <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a course') }}</option>
        @foreach ($courses as $availableCourse)
            <option value="{{ $availableCourse->id }}" @selected((int) old('course_id', $payment?->course_id) === $availableCourse->id)>{{ $availableCourse->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
</div>

<div>
    <x-input-label for="amount" :value="__('Amount')" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $payment?->amount)" required />
    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
</div>

<div>
    <x-input-label for="payment_date" :value="__('Payment Date')" />
    <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" :value="old('payment_date', optional($payment?->payment_date)->format('Y-m-d'))" required />
    <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
</div>

<div>
    <x-input-label for="payment_method" :value="__('Payment Method')" />
    <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
            <option value="{{ $value }}" @selected(old('payment_method', $payment?->payment_method) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (['paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed', 'refunded' => 'Refunded'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $payment?->status ?? 'paid') === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>

<div>
    <x-input-label for="reference_number" :value="__('Reference Number')" />
    <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number', $payment?->reference_number)" />
    <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
</div>

<div>
    <x-input-label for="notes" :value="__('Notes')" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes', $payment?->notes) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
</div>
