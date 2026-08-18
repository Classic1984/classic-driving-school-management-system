@php($legend = $legend ?? __('Course Enrollment & Initial Payment'))

<fieldset class="border border-gray-200 rounded-md p-4">
    <legend class="text-sm font-medium text-gray-700 px-1">{{ $legend }}</legend>

    <div class="space-y-6">
        <div>
            <x-input-label for="course_id" :value="__('Course')" />
            <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                <option value="">{{ __('Select a course') }}</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) old('course_id') === (string) $course->id)>{{ $course->name }} — {{ $course->isWeekend() ? 'Weekend' : 'Weekday' }} (₦{{ number_format($course->fee, 2) }})</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" id="starts_double_period" name="starts_double_period" value="1" @checked(old('starts_double_period')) class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                {{ __('Starting Double Period training immediately (weekday courses only)') }}
            </label>
            <p class="mt-1 text-xs text-gray-500">{{ __('Double Period covers 4 training days in 2 calendar days, so the balance due date is shortened to 2 days instead of the course\'s usual grace period.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('starts_double_period')" />
        </div>

        @if (auth()->user()->isDirector())
            <div>
                <x-input-label for="discount_choice" :value="__('Discount')" />
                <select id="discount_choice" name="discount_choice" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                    <option value="">{{ __('No Discount') }}</option>
                    @foreach (config('discounts.standard_presets') as $preset)
                        <option value="{{ $preset }}" @selected(old('discount_choice') === (string) $preset)>₦{{ number_format($preset) }}</option>
                    @endforeach
                    @foreach (config('discounts.director_presets') as $preset)
                        <option value="{{ $preset }}" @selected(old('discount_choice') === (string) $preset)>₦{{ number_format($preset) }}</option>
                    @endforeach
                    <option value="custom" @selected(old('discount_choice') === 'custom')>{{ __('Custom') }}</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('discount_choice')" />
            </div>

            <div id="custom-discount-fields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="custom_discount_percentage" :value="__('Custom Percentage (%)')" />
                    <x-text-input id="custom_discount_percentage" name="custom_discount_percentage" type="number" step="0.01" min="0.01" max="100" class="mt-1 block w-full" :value="old('custom_discount_percentage')" />
                    <x-input-error class="mt-2" :messages="$errors->get('custom_discount_percentage')" />
                </div>
                <div>
                    <x-input-label for="custom_discount_amount" :value="__('OR Fixed Amount (₦)')" />
                    <x-text-input id="custom_discount_amount" name="custom_discount_amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('custom_discount_amount')" />
                    <x-input-error class="mt-2" :messages="$errors->get('custom_discount_amount')" />
                </div>
                <p class="sm:col-span-2 text-xs text-gray-500">{{ __('Enter either a percentage or a fixed amount, not both.') }}</p>
            </div>

            <div id="discount-reason-wrapper" class="hidden space-y-6">
                <div>
                    <x-input-label for="discount_reason" :value="__('Reason for Discount')" />
                    <select id="discount_reason" name="discount_reason" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                        <option value="">{{ __('Select') }}</option>
                        @foreach (config('discounts.reasons') as $value => $label)
                            <option value="{{ $value }}" @selected(old('discount_reason') === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('discount_reason')" />
                </div>

                <div id="discount-reason-note-wrapper" class="hidden">
                    <x-input-label for="discount_reason_note" :value="__('Please Specify')" />
                    <x-text-input id="discount_reason_note" name="discount_reason_note" type="text" class="mt-1 block w-full" :value="old('discount_reason_note')" />
                    <x-input-error class="mt-2" :messages="$errors->get('discount_reason_note')" />
                </div>
            </div>
        @endif

        <div id="fee-preview" class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm space-y-1">
            <div class="flex justify-between"><span>{{ __('Package Fee') }}</span><span id="preview-package-fee">₦0.00</span></div>
            <div class="flex justify-between"><span>{{ __('Discount') }}</span><span id="preview-discount">₦0.00</span></div>
            <div class="flex justify-between font-semibold border-t border-gray-300 pt-1 mt-1"><span>{{ __('Final Course Fee') }}</span><span id="preview-final-fee">₦0.00</span></div>
        </div>

        <script>
            (function () {
                var courseFees = @json($courses->pluck('fee', 'id'));
                var courseSelect = document.getElementById('course_id');
                var discountSelect = document.getElementById('discount_choice');
                var customPercentageInput = document.getElementById('custom_discount_percentage');
                var customAmountInput = document.getElementById('custom_discount_amount');
                var customFieldsWrapper = document.getElementById('custom-discount-fields');
                var reasonWrapper = document.getElementById('discount-reason-wrapper');
                var reasonSelect = document.getElementById('discount_reason');
                var reasonNoteWrapper = document.getElementById('discount-reason-note-wrapper');
                var previewPackageFee = document.getElementById('preview-package-fee');
                var previewDiscount = document.getElementById('preview-discount');
                var previewFinalFee = document.getElementById('preview-final-fee');

                function formatNaira(amount) {
                    return '₦' + amount.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function currentFee() {
                    return parseFloat(courseFees[courseSelect.value] || 0);
                }

                function recalculate() {
                    var fee = currentFee();
                    // Non-Director users don't get a discount_choice field at
                    // all (discounts are Director-only), so there's nothing to
                    // subtract for them.
                    var choice = discountSelect ? discountSelect.value : '';
                    var discountAmount = 0;

                    if (choice === 'custom') {
                        if (customFieldsWrapper) customFieldsWrapper.classList.remove('hidden');
                        var amount = parseFloat(customAmountInput ? customAmountInput.value : '') || 0;
                        var percentage = parseFloat(customPercentageInput ? customPercentageInput.value : '') || 0;
                        if (amount > 0) {
                            discountAmount = Math.min(amount, fee);
                        } else if (percentage > 0) {
                            discountAmount = fee * percentage / 100;
                        }
                    } else {
                        if (customFieldsWrapper) customFieldsWrapper.classList.add('hidden');
                        var presetAmount = parseFloat(choice) || 0;
                        discountAmount = Math.min(presetAmount, fee);
                    }

                    if (reasonWrapper) reasonWrapper.classList.toggle('hidden', ! choice);

                    previewPackageFee.textContent = formatNaira(fee);
                    previewDiscount.textContent = formatNaira(discountAmount);
                    previewFinalFee.textContent = formatNaira(Math.max(0, fee - discountAmount));
                }

                function toggleReasonNote() {
                    if (reasonNoteWrapper) reasonNoteWrapper.classList.toggle('hidden', reasonSelect.value !== 'other');
                }

                courseSelect.addEventListener('change', recalculate);
                if (discountSelect) discountSelect.addEventListener('change', recalculate);
                if (customPercentageInput) customPercentageInput.addEventListener('input', recalculate);
                if (customAmountInput) customAmountInput.addEventListener('input', recalculate);
                if (reasonSelect) reasonSelect.addEventListener('change', toggleReasonNote);

                recalculate();
                toggleReasonNote();
            })();
        </script>

        <div>
            <x-input-label for="amount_paid" :value="__('Amount Paid Now')" />
            <x-text-input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount_paid')" />
            <p class="mt-1 text-xs text-gray-500">{{ __('Leave blank if no payment is being made yet.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('amount_paid')" />
        </div>

        <div>
            <x-input-label for="payment_method" :value="__('Payment Method')" />
            <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <option value="">{{ __('Select') }}</option>
                @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
        </div>
    </div>
</fieldset>
