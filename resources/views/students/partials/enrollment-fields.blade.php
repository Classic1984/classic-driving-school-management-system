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

        @isset($additionalOffers)
            @if ($additionalOffers->isNotEmpty())
                <div>
                    <x-input-label :value="__('Additional Offers')" />
                    <div class="mt-2 space-y-2">
                        @foreach ($additionalOffers as $offer)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    name="service_ids[]"
                                    value="{{ $offer->id }}"
                                    class="offer-checkbox rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500"
                                    data-service-id="{{ $offer->id }}"
                                    @checked(collect(old('service_ids', []))->contains((string) $offer->id))
                                >
                                {{ $offer->name }} (₦{{ number_format($offer->price, 2) }})
                            </label>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('service_ids')" />
                </div>
            @endif
        @endisset

        <div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" id="starts_double_period" name="starts_double_period" value="1" @checked(old('starts_double_period')) class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                {{ __('Starting Double Period training immediately (weekday courses only)') }}
            </label>
            <p class="mt-1 text-xs text-gray-500">{{ __('Double Period covers 4 training days in 2 calendar days, so the balance due date is shortened to 2 days instead of the course\'s usual grace period.') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('starts_double_period')" />
        </div>

        <div>
            <x-input-label for="discount_choice" :value="__('Discount')" />
            <select id="discount_choice" name="discount_choice" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <option value="">{{ __('No Discount') }}</option>
                @foreach (config('discounts.standard_presets') as $preset)
                    <option value="{{ $preset }}" @selected(old('discount_choice') === (string) $preset)>₦{{ number_format($preset) }}</option>
                @endforeach
                @if (auth()->user()->isDirector())
                    @foreach (config('discounts.director_presets') as $preset)
                        <option value="{{ $preset }}" @selected(old('discount_choice') === (string) $preset)>₦{{ number_format($preset) }}</option>
                    @endforeach
                    <option value="custom" @selected(old('discount_choice') === 'custom')>{{ __('Custom (Director Only)') }}</option>
                @endif
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('discount_choice')" />
            @unless (auth()->user()->isDirector())
                <p class="mt-1 text-xs text-amber-600">{{ __('A Director must approve this discount before it applies - the student is enrolled at the full fee until then.') }}</p>
            @endunless
        </div>

        @if (auth()->user()->isDirector())
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
        @endif

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

        <div id="fee-preview" class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm space-y-1">
            <div class="flex justify-between"><span>{{ __('Package Fee') }}</span><span id="preview-package-fee">₦0.00</span></div>
            <div class="flex justify-between"><span>{{ __('Discount') }}</span><span id="preview-discount">₦0.00</span></div>
            <div class="flex justify-between font-semibold border-t border-gray-300 pt-1 mt-1"><span>{{ __('Final Course Fee') }}</span><span id="preview-final-fee">₦0.00</span></div>
            <div id="preview-offers-wrapper" class="hidden">
                <div class="flex justify-between"><span>{{ __('Additional Offers') }}</span><span id="preview-offers-total">₦0.00</span></div>
                <div class="flex justify-between font-semibold border-t border-gray-300 pt-1 mt-1"><span>{{ __('Total Package') }}</span><span id="preview-total-package">₦0.00</span></div>
            </div>
        </div>

        <div id="simple-payment-fields">
            <div>
                <x-input-label for="amount_paid" :value="__('Amount Paid Now')" />
                <x-text-input id="amount_paid" name="amount_paid" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount_paid')" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Leave blank if no payment is being made yet.') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('amount_paid')" />
            </div>
        </div>

        @isset($additionalOffers)
            @if ($additionalOffers->isNotEmpty())
                <div id="allocation-payment-fields" class="hidden space-y-4">
                    <div>
                        <x-input-label :value="__('Amount to Pay Now')" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Optional, per item. Leave any row blank to keep that charge outstanding for now.') }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Charge') }}</th>
                                    <th class="px-2 py-1">{{ __('Price') }}</th>
                                    <th class="px-2 py-1">{{ __('Amount to Pay') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr>
                                    <td class="px-2 py-1">{{ __('Training') }}</td>
                                    <td class="px-2 py-1" id="allocation-training-price">₦0.00</td>
                                    <td class="px-2 py-1">
                                        <input type="number" id="training_amount_input" name="training_amount" step="0.01" min="0" class="block w-32 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" value="{{ old('training_amount') }}">
                                        <x-input-error class="mt-1" :messages="$errors->get('training_amount')" />
                                    </td>
                                </tr>
                                @foreach ($additionalOffers as $offer)
                                    <tr class="allocation-offer-row hidden" data-service-id="{{ $offer->id }}">
                                        <td class="px-2 py-1">{{ $offer->name }}</td>
                                        <td class="px-2 py-1">₦{{ number_format($offer->price, 2) }}</td>
                                        <td class="px-2 py-1">
                                            <input type="number" name="service_amounts[{{ $offer->id }}]" step="0.01" min="0" max="{{ $offer->price }}" class="service-amount-input block w-32 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" value="{{ old("service_amounts.{$offer->id}") }}">
                                            <x-input-error class="mt-1" :messages="$errors->get('service_amounts.'.$offer->id)" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-sm space-y-1">
                        <div class="flex justify-between font-medium"><span>{{ __('Amount Paid') }}</span><span id="preview-amount-paid">₦0.00</span></div>
                        <div class="flex justify-between font-semibold border-t border-gray-300 pt-1 mt-1"><span>{{ __('Balance') }}</span><span id="preview-balance">₦0.00</span></div>
                    </div>
                </div>
            @endif
        @endisset

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

        <script>
            (function () {
                var courseFees = @json($courses->pluck('fee', 'id'));
                var offerPrices = @json(($additionalOffers ?? collect())->pluck('price', 'id'));
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
                var previewOffersWrapper = document.getElementById('preview-offers-wrapper');
                var previewOffersTotal = document.getElementById('preview-offers-total');
                var previewTotalPackage = document.getElementById('preview-total-package');
                var offerCheckboxes = document.querySelectorAll('.offer-checkbox');
                var simplePaymentFields = document.getElementById('simple-payment-fields');
                var allocationPaymentFields = document.getElementById('allocation-payment-fields');
                var trainingAmountInput = document.getElementById('training_amount_input');
                var allocationTrainingPrice = document.getElementById('allocation-training-price');
                var previewAmountPaid = document.getElementById('preview-amount-paid');
                var previewBalance = document.getElementById('preview-balance');

                function formatNaira(amount) {
                    return '₦' + amount.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                function currentFee() {
                    return parseFloat(courseFees[courseSelect.value] || 0);
                }

                function tickedOfferIds() {
                    return Array.prototype.filter.call(offerCheckboxes, function (box) {
                        return box.checked;
                    }).map(function (box) {
                        return box.dataset.serviceId;
                    });
                }

                function offersTotal(ids) {
                    return ids.reduce(function (sum, id) {
                        return sum + (parseFloat(offerPrices[id]) || 0);
                    }, 0);
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

                    var finalFee = Math.max(0, fee - discountAmount);

                    previewPackageFee.textContent = formatNaira(fee);
                    previewDiscount.textContent = formatNaira(discountAmount);
                    previewFinalFee.textContent = formatNaira(finalFee);

                    var ids = tickedOfferIds();
                    var hasOffers = ids.length > 0;
                    var offersSum = offersTotal(ids);
                    var totalPackage = finalFee + offersSum;

                    if (previewOffersWrapper) {
                        previewOffersWrapper.classList.toggle('hidden', ! hasOffers);
                        previewOffersTotal.textContent = formatNaira(offersSum);
                        previewTotalPackage.textContent = formatNaira(totalPackage);
                    }

                    if (simplePaymentFields) simplePaymentFields.classList.toggle('hidden', hasOffers);
                    if (allocationPaymentFields) allocationPaymentFields.classList.toggle('hidden', ! hasOffers);
                    if (allocationTrainingPrice) allocationTrainingPrice.textContent = formatNaira(finalFee);
                    if (trainingAmountInput) trainingAmountInput.max = finalFee;

                    // Show/hide each offer's allocation row to match its
                    // checkbox, clearing any leftover amount for a row that
                    // just got unticked so it doesn't submit silently.
                    Array.prototype.forEach.call(document.querySelectorAll('.allocation-offer-row'), function (row) {
                        var ticked = ids.indexOf(row.dataset.serviceId) !== -1;
                        row.classList.toggle('hidden', ! ticked);
                        if (! ticked) {
                            var input = row.querySelector('.service-amount-input');
                            if (input) input.value = '';
                        }
                    });

                    updateAmountPaidAndBalance(totalPackage, hasOffers);
                }

                function updateAmountPaidAndBalance(totalPackage, hasOffers) {
                    if (! previewAmountPaid || ! previewBalance) return;

                    var amountPaid = 0;

                    if (hasOffers) {
                        amountPaid += parseFloat(trainingAmountInput ? trainingAmountInput.value : 0) || 0;
                        Array.prototype.forEach.call(document.querySelectorAll('.allocation-offer-row:not(.hidden) .service-amount-input'), function (input) {
                            amountPaid += parseFloat(input.value) || 0;
                        });
                    }

                    previewAmountPaid.textContent = formatNaira(amountPaid);
                    previewBalance.textContent = formatNaira(Math.max(0, totalPackage - amountPaid));
                }

                function toggleReasonNote() {
                    if (reasonNoteWrapper) reasonNoteWrapper.classList.toggle('hidden', reasonSelect.value !== 'other');
                }

                courseSelect.addEventListener('change', recalculate);
                if (discountSelect) discountSelect.addEventListener('change', recalculate);
                if (customPercentageInput) customPercentageInput.addEventListener('input', recalculate);
                if (customAmountInput) customAmountInput.addEventListener('input', recalculate);
                if (reasonSelect) reasonSelect.addEventListener('change', toggleReasonNote);
                Array.prototype.forEach.call(offerCheckboxes, function (box) {
                    box.addEventListener('change', recalculate);
                });
                if (trainingAmountInput) trainingAmountInput.addEventListener('input', recalculate);
                Array.prototype.forEach.call(document.querySelectorAll('.service-amount-input'), function (input) {
                    input.addEventListener('input', recalculate);
                });

                recalculate();
                toggleReasonNote();
            })();
        </script>
    </div>
</fieldset>
