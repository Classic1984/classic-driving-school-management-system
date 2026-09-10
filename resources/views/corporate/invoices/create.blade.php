<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Invoice') }}
        </h2>
    </x-slot>

    @php
        $receiptIconPath = ['M9 14.25 6.75 12l2.25-2.25M15 9.75l2.25 2.25-2.25 2.25M3.375 21h17.25c.621 0 1.125-.504 1.125-1.125V4.125C21.75 3.504 21.246 3 20.625 3H3.375C2.754 3 2.25 3.504 2.25 4.125v15.75c0 .621.504 1.125 1.125 1.125Z'];
        $trashIconPath = 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0';
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
    @endphp

    <div
        class="py-6"
        x-data="{
            items: [{ description: '', quantity: 1, unit_price: '' }],
            addItem() { this.items.push({ description: '', quantity: 1, unit_price: '' }); },
            removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); },
            amount(item) { const q = parseFloat(item.quantity) || 0; const p = parseFloat(item.unit_price) || 0; return (q * p).toLocaleString(); },
            total() { return this.items.reduce((sum, item) => sum + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0).toLocaleString(); },
        }"
    >
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Create Invoice') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Bill a corporate client directly for training already agreed.') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($receiptIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
            </div>

            <form method="post" action="{{ route('corporate-invoices.store') }}" class="space-y-6">
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
                            <x-input-label for="invoice_date" :value="__('Invoice Date')" />
                            <x-text-input id="invoice_date" name="invoice_date" type="date" class="block w-full mt-1" :value="old('invoice_date', now()->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('invoice_date')" />
                        </div>
                        <div>
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input id="due_date" name="due_date" type="date" class="block w-full mt-1" :value="old('due_date', now()->addWeek()->format('Y-m-d'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 space-y-6">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500">{{ __('Training Details') }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <x-input-label for="programme_name" :value="__('Programme')" />
                            <x-text-input id="programme_name" name="programme_name" type="text" class="block w-full mt-1" placeholder="{{ __('e.g. Defensive Driving') }}" :value="old('programme_name')" />
                        </div>
                        <div>
                            <x-input-label for="duration_label" :value="__('Duration')" />
                            <x-text-input id="duration_label" name="duration_label" type="text" class="block w-full mt-1" placeholder="{{ __('e.g. Two Weeks') }}" :value="old('duration_label')" />
                        </div>
                        <div>
                            <x-input-label for="participant_count" :value="__('Number of Drivers')" />
                            <x-text-input id="participant_count" name="participant_count" type="number" min="1" class="block w-full mt-1" :value="old('participant_count')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="course_coverage" :value="__('Course Coverage (optional, one topic per line)')" />
                        <textarea id="course_coverage" name="course_coverage" rows="4" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" placeholder="{{ __("Defensive Driving Principles\nHazard Identification & Risk Management") }}">{{ old('course_coverage') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('course_coverage')" />
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
                                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" required>
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
                        {{ __('Save Invoice') }}
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
