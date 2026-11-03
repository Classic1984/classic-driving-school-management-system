<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice Settings') }}
        </h2>
    </x-slot>

    @php
        $cogIconPath = ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'];
        $buildingIconPath = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
        $bankIconPath = 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m3-3h.75m-.75 3h.75m3-3h.75m-.75 3h.75M6 21V9.75a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 .75.75V21M1.5 9.75l10.5-6 10.5 6';
        $hashtagIconPath = 'M5.25 9h13.5m-13.5 6.75h13.5M8.25 3v18m7.5-18v18';
        $signatureIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM3 19.5c0-3.728 3.582-6.75 8-6.75s8 3.022 8 6.75';
        $listIconPath = 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z';
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($cogIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Invoice Settings') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Company, bank, and numbering details printed on every corporate quotation, invoice, and receipt.') }}</p>
                </div>
            </div>

            @if (session('status') === 'settings-updated')
                <p class="text-sm font-medium text-green-600">{{ __('Invoice settings updated successfully.') }}</p>
            @endif

            <form method="post" action="{{ route('corporate-invoice-settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('put')

                <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
                        {{ __('Company Details') }}
                    </h4>
                    <div class="max-w-xl space-y-6">
                        <div>
                            <x-input-label for="company_name" :value="__('Company Name')" />
                            <x-text-input id="company_name" name="company_name" type="text" class="block w-full mt-1" placeholder="{{ __('Classic Driving School & Son Nigeria Limited') }}" :value="old('company_name', $settings->company_name)" />
                            <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
                        </div>
                        <div>
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" class="block w-full mt-1" :value="old('address', $settings->address)" />
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="block w-full mt-1" :value="old('phone', $settings->phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="block w-full mt-1" :value="old('email', $settings->email)" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="website" :value="__('Website')" />
                            <x-text-input id="website" name="website" type="text" class="block w-full mt-1" placeholder="classicdriving.com.ng" :value="old('website', $settings->website)" />
                            <x-input-error class="mt-2" :messages="$errors->get('website')" />
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bankIconPath }}" /></svg>
                        {{ __('Payment Details') }}
                    </h4>
                    <div class="max-w-xl space-y-6">
                        <div>
                            <x-input-label for="bank_account_name" :value="__('Account Name')" />
                            <x-text-input id="bank_account_name" name="bank_account_name" type="text" class="block w-full mt-1" :value="old('bank_account_name', $settings->bank_account_name)" />
                            <x-input-error class="mt-2" :messages="$errors->get('bank_account_name')" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="bank_name" :value="__('Bank')" />
                                <x-text-input id="bank_name" name="bank_name" type="text" class="block w-full mt-1" :value="old('bank_name', $settings->bank_name)" />
                                <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                            </div>
                            <div>
                                <x-input-label for="bank_account_number" :value="__('Account Number')" />
                                <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="block w-full mt-1" :value="old('bank_account_number', $settings->bank_account_number)" />
                                <x-input-error class="mt-2" :messages="$errors->get('bank_account_number')" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500 mb-1">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $hashtagIconPath }}" /></svg>
                        {{ __('Document Numbering') }}
                    </h4>
                    <p class="text-xs text-gray-400 mb-4">{{ __('The number itself is always assigned automatically and resets every year - these prefixes are optional labels, and default to QUO/INV/REC if left blank.') }}</p>
                    <div class="max-w-xl grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="quotation_prefix" :value="__('Quotation Prefix (optional)')" />
                            <x-text-input id="quotation_prefix" name="quotation_prefix" type="text" class="block w-full mt-1" placeholder="QUO" :value="old('quotation_prefix', $settings->quotation_prefix)" />
                            <p class="mt-1 text-xs text-gray-400">{{ __('e.g. :example', ['example' => ($settings->quotation_prefix ?: 'QUO').'-2026-00001']) }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('quotation_prefix')" />
                        </div>
                        <div>
                            <x-input-label for="invoice_prefix" :value="__('Invoice Prefix (optional)')" />
                            <x-text-input id="invoice_prefix" name="invoice_prefix" type="text" class="block w-full mt-1" placeholder="INV" :value="old('invoice_prefix', $settings->invoice_prefix)" />
                            <p class="mt-1 text-xs text-gray-400">{{ __('e.g. :example', ['example' => ($settings->invoice_prefix ?: 'INV').'-2026-00001']) }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('invoice_prefix')" />
                        </div>
                        <div>
                            <x-input-label for="receipt_prefix" :value="__('Receipt Prefix (optional)')" />
                            <x-text-input id="receipt_prefix" name="receipt_prefix" type="text" class="block w-full mt-1" placeholder="REC" :value="old('receipt_prefix', $settings->receipt_prefix)" />
                            <p class="mt-1 text-xs text-gray-400">{{ __('e.g. :example', ['example' => ($settings->receipt_prefix ?: 'REC').'-2026-00001']) }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('receipt_prefix')" />
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500 mb-1">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $listIconPath }}" /></svg>
                        {{ __('Training Details Presets') }}
                    </h4>
                    <p class="text-xs text-gray-400 mb-4">{{ __('One value per line. These show up as suggestions on the Programme, Duration, Number of Drivers, and Charges fields when creating a quotation or invoice - typing something else is always fine too.') }}</p>
                    <div class="max-w-3xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <x-input-label for="programme_options" :value="__('Programmes')" />
                            <textarea id="programme_options" name="programme_options" rows="4" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __("Defensive Driving\nBasic Driving\nAdvanced Driving") }}">{{ old('programme_options', $settings->programme_options) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('programme_options')" />
                        </div>
                        <div>
                            <x-input-label for="duration_options" :value="__('Durations')" />
                            <textarea id="duration_options" name="duration_options" rows="4" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __("One Week\nTwo Weeks\nOne Day") }}">{{ old('duration_options', $settings->duration_options) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('duration_options')" />
                        </div>
                        <div>
                            <x-input-label for="driver_count_options" :value="__('Number of Drivers')" />
                            <textarea id="driver_count_options" name="driver_count_options" rows="4" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __("1\n5\n10") }}">{{ old('driver_count_options', $settings->driver_count_options) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('driver_count_options')" />
                        </div>
                        <div>
                            <x-input-label for="service_options" :value="__('Charges')" />
                            <textarea id="service_options" name="service_options" rows="4" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __("Certificate of Completion\nRegistration Fee") }}">{{ old('service_options', $settings->service_options) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('service_options')" />
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-6 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $signatureIconPath }}" /></svg>
                        {{ __("Director's Signature") }}
                    </h4>
                    <div class="max-w-xl">
                        @if ($settings->signature_path)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ Storage::disk('public')->url($settings->signature_path) }}" alt="{{ __('Signature') }}" class="h-16 rounded border border-gray-200 bg-gray-50 px-3">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                                    <input type="checkbox" name="remove_signature" value="1" class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    {{ __('Remove signature') }}
                                </label>
                            </div>
                        @else
                            <p class="mb-3 text-sm text-gray-500">{{ __('No signature uploaded yet - invoices will show a blank signature line.') }}</p>
                        @endif
                        <input type="file" name="signature" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        <x-input-error class="mt-2" :messages="$errors->get('signature')" />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        {{ __('Save Settings') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
