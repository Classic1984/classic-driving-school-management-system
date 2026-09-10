<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quotation') }}
        </h2>
    </x-slot>

    @php
        $documentIconPath = ['M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'];
        $trashIconPath = 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0';
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $chevronDownIconPath = 'm19.5 8.25-7.5 7.5-7.5-7.5';
    @endphp

    <div
        class="py-6"
        x-data="{
            items: [{ description: '', quantity: 1, unit_price: '', showSuggestions: false, menuTop: 0, menuLeft: 0, menuWidth: 0 }],
            serviceOptions: @js($serviceOptions),
            addItem() { this.items.push({ description: '', quantity: 1, unit_price: '', showSuggestions: false, menuTop: 0, menuLeft: 0, menuWidth: 0 }); },
            removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
            amount(item) { const q = parseFloat(item.quantity) || 0; const p = parseFloat(item.unit_price) || 0; return (q * p).toLocaleString(); },
            total() { return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0).toLocaleString(); },
            filteredServiceOptions(item) {
                return item.description === ''
                    ? this.serviceOptions
                    : this.serviceOptions.filter((option) => option.toLowerCase().includes(item.description.toLowerCase()));
            },
            // The suggestion list is teleported to <body> (see the field
            // markup below) so it isn't clipped by the Charges table's
            // horizontal-scroll wrapper - overflow-x-auto forces
            // overflow-y to auto too (per the CSS overflow interop rule),
            // which would otherwise cut the dropdown off a few pixels
            // below the input. Teleporting means it's no longer
            // positioned by CSS relative to the input, so its coordinates
            // are computed from the input's own bounding box instead.
            openSuggestions(item, event) {
                const rect = event.target.getBoundingClientRect();
                item.menuTop = rect.bottom + window.scrollY;
                item.menuLeft = rect.left + window.scrollX;
                item.menuWidth = rect.width;
                item.showSuggestions = true;
            },
            selectService(item, option) { item.description = option; item.showSuggestions = false; },
        }"
    >
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Create Quotation') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Give a corporate client a price before they commit to an invoice.') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($documentIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
            </div>

            <form method="post" action="{{ route('corporate-quotations.store') }}" class="space-y-6">
                @csrf

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 space-y-6">
                    <div>
                        <x-input-label for="corporate_company_id" :value="__('Company')" />
                        <select id="corporate_company_id" name="corporate_company_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('Select a company…') }}</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected(old('corporate_company_id', $selectedCompanyId) == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('corporate_company_id')" />
                        @if ($companies->isEmpty())
                            <p class="mt-2 text-sm text-amber-600">
                                {{ __('No companies registered yet.') }}
                                <a href="{{ route('corporate-companies.create') }}" class="underline hover:no-underline">{{ __('Add one first') }}</a>.
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="issue_date" :value="__('Issue Date')" />
                            <x-text-input id="issue_date" name="issue_date" type="date" class="block w-full mt-1" :value="old('issue_date', now()->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('issue_date')" />
                        </div>
                        <div>
                            <x-input-label for="valid_until" :value="__('Valid Until (optional)')" />
                            <x-text-input id="valid_until" name="valid_until" type="date" class="block w-full mt-1" :value="old('valid_until', now()->addDays(30)->format('Y-m-d'))" />
                            <x-input-error class="mt-2" :messages="$errors->get('valid_until')" />
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 space-y-6">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500">{{ __('Training Details (optional)') }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="programme_name" :value="__('Programme')" />
                            <x-combobox id="programme_name" name="programme_name" :options="$programmeOptions" :value="old('programme_name')" placeholder="{{ __('e.g. Defensive Driving') }}" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="duration_label" :value="__('Duration')" />
                            <x-combobox id="duration_label" name="duration_label" :options="$durationOptions" :value="old('duration_label')" placeholder="{{ __('e.g. Two Weeks') }}" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="participant_count" :value="__('Number of Drivers')" />
                            <x-combobox id="participant_count" name="participant_count" type="number" min="1" :options="$driverCountOptions" :value="old('participant_count')" class="mt-1" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="course_coverage" :value="__('Course Coverage (optional, one topic per line)')" />
                        <textarea id="course_coverage" name="course_coverage" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('course_coverage') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('course_coverage')" />
                    </div>
                    <div>
                        <x-input-label for="notes" :value="__('Notes (optional)')" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500">{{ __('Charges') }}</h4>
                        <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                            {{ __('Add Item') }}
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    <th class="pb-2 pr-3">{{ __('Description') }}</th>
                                    <th class="pb-2 px-3 w-24">{{ __('Qty') }}</th>
                                    <th class="pb-2 px-3 w-36">{{ __('Unit Price (₦)') }}</th>
                                    <th class="pb-2 pl-3 w-32 text-right">{{ __('Amount (₦)') }}</th>
                                    <th class="pb-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr class="border-t border-gray-100">
                                        <td class="py-2 pr-3">
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    :name="`items[${index}][description]`"
                                                    x-model="item.description"
                                                    x-on:focus="openSuggestions(item, $event)"
                                                    x-on:input="openSuggestions(item, $event)"
                                                    x-on:blur="item.showSuggestions = false"
                                                    autocomplete="off"
                                                    class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm pr-8"
                                                    required
                                                >
                                                <button type="button" x-on:mousedown.prevent="item.showSuggestions = !item.showSuggestions" tabindex="-1" class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chevronDownIconPath }}" /></svg>
                                                </button>
                                                <template x-teleport="body">
                                                    <ul
                                                        x-show="item.showSuggestions"
                                                        x-cloak
                                                        :style="`position:absolute; top:${item.menuTop}px; left:${item.menuLeft}px; width:${item.menuWidth}px;`"
                                                        class="z-50 mt-1 max-h-48 overflow-auto rounded-md bg-white py-1 text-sm shadow-lg ring-1 ring-gray-200"
                                                    >
                                                        <template x-for="option in filteredServiceOptions(item)" :key="option">
                                                            <li x-on:mousedown.prevent="selectService(item, option)" x-text="option" class="cursor-pointer px-3 py-2 text-gray-700 hover:bg-amber-50"></li>
                                                        </template>
                                                        <li x-show="serviceOptions.length === 0" class="px-3 py-2 text-gray-400 italic">
                                                            {{ __('No suggestions yet.') }}
                                                            <a href="{{ route('corporate-invoice-settings.edit') }}" class="not-italic text-amber-600 hover:underline">{{ __('Add some in Invoice Settings') }}</a>
                                                        </li>
                                                        <li x-show="serviceOptions.length > 0 && filteredServiceOptions(item).length === 0" class="px-3 py-2 text-gray-400 italic">{{ __('No matches - your typed value will still be used.') }}</li>
                                                    </ul>
                                                </template>
                                            </div>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="0.01" min="0.01" :name="`items[${index}][quantity]`" x-model="item.quantity" class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="0.01" min="0" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
                                        </td>
                                        <td class="py-2 pl-3 text-right text-sm font-semibold text-gray-900" x-text="amount(item)"></td>
                                        <td class="py-2 text-right">
                                            <button type="button" @click="removeItem(index)" class="text-gray-400 hover:text-red-600" x-show="items.length > 1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trashIconPath }}" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('items')" />

                    <div class="flex justify-end mt-4 pt-4 border-t border-gray-100">
                        <p class="text-sm font-bold text-gray-900">{{ __('Total:') }} ₦<span x-text="total()"></span></p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                        {{ __('Save Quotation') }}
                    </button>
                    <a href="{{ $selectedCompanyId ? route('corporate-companies.show', $selectedCompanyId) : route('corporate-companies.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 hover:bg-gray-50 px-5 py-3 text-sm font-semibold text-gray-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
