{{--
    Shared payment section for both tabs of service-applications/index.blade.php.
    Expects $service in scope, and $idPrefix (unique per including tab, so
    the two forms on that one page - normally hidden from each other via
    x-show, not actually removed from the DOM - never render duplicate
    element ids). Defaults the amount to the service's full price - staff
    can lower it for a part payment.
--}}
<div class="rounded-xl bg-amber-50/40 ring-1 ring-amber-200 p-4 sm:p-6 space-y-6">
    <h4 class="text-sm font-bold text-gray-900">{{ __('Payment') }}</h4>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <x-input-label :for="$idPrefix.'_amount'" :value="__('Amount (₦)')" />
            <x-text-input :id="$idPrefix.'_amount'" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', (string) $service->price)" required />
            <p class="mt-1 text-xs text-gray-500">{{ __('Full price is :price - lower this for a part payment.', ['price' => number_format($service->price, 2)]) }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('amount')" />
        </div>

        <div>
            <x-input-label :for="$idPrefix.'_payment_method'" :value="__('Payment Method')" />
            <select id="{{ $idPrefix }}_payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
        </div>

        <div>
            <x-input-label :for="$idPrefix.'_payment_date'" :value="__('Payment Date')" />
            <x-text-input :id="$idPrefix.'_payment_date'" name="payment_date" type="date" class="mt-1 block w-full" :value="old('payment_date', now()->toDateString())" :max="now()->format('Y-m-d')" required />
            <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
        </div>

        <div>
            <x-input-label :for="$idPrefix.'_reference_number'" :value="__('Reference Number')" />
            <x-text-input :id="$idPrefix.'_reference_number'" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number')" />
            <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
        </div>
    </div>

    <div>
        <x-input-label :for="$idPrefix.'_notes'" :value="__('Notes')" />
        <textarea id="{{ $idPrefix }}_notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
    </div>
</div>
