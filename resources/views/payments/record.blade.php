<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Payment') }}
        </h2>
    </x-slot>

    @php
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>

                    <div class="relative flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Record Payment') }}</h3>
                            <p class="text-sm text-gray-400">{{ __('Pick a student, then tick off the charges being paid') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8">
                    <form method="get" action="{{ route('payments.record.create') }}" class="flex items-end gap-4">
                        <div class="flex-1">
                            <x-input-label for="student_id" :value="__('Student')" />
                            <x-select id="student_id" name="student_id" class="mt-1 block w-full" required>
                                <option value="">{{ __('Select a student') }}</option>
                                @foreach ($students as $availableStudent)
                                    <option value="{{ $availableStudent->id }}" @selected($student?->id === $availableStudent->id)>{{ $availableStudent->name }} ({{ $availableStudent->student_id_number }})</option>
                                @endforeach
                            </x-select>
                        </div>

                        <x-secondary-button type="submit">{{ __('Load Charges') }}</x-secondary-button>
                    </form>
                </div>
            </div>

            @if ($student)
                <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold-100 text-gold-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        </span>
                        <h3 class="text-lg font-bold text-navy-900">{{ $student->name }} ({{ $student->student_id_number }})</h3>
                    </div>

                    @if ($charges->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('This student has no outstanding charges.') }}</p>
                    @else
                        @php
                            $rowsData = $charges->values()->map(function ($charge, $index) use ($preselect) {
                                $isPreselected = $preselect
                                    && $preselect['type'] === $charge['type']
                                    && (int) $preselect['id'] === (int) $charge['id'];

                                return [
                                    'amount' => old("allocations.{$index}.amount") ?? ($isPreselected ? (string) $charge['balance'] : ''),
                                    'balance' => (string) $charge['balance'],
                                ];
                            })->all();
                        @endphp
                        <form
                            method="post"
                            action="{{ route('payments.record.store') }}"
                            class="space-y-6"
                            x-data="{
                                rows: @js($rowsData),
                                get total() {
                                    return this.rows.reduce((sum, row) => sum + (parseFloat(row.amount) || 0), 0).toFixed(2);
                                },
                                toggle(i, checked) {
                                    this.rows[i].amount = checked ? this.rows[i].balance : '';
                                },
                            }"
                        >
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">

                            <x-input-error class="mb-2" :messages="$errors->get('allocations')" />

                            <div class="overflow-hidden rounded-xl ring-1 ring-line">
                                <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-line">
                                    <thead>
                                        <tr class="bg-navy-50 text-left text-xs font-semibold uppercase tracking-wider text-navy-700">
                                            <th class="px-3 py-3"></th>
                                            <th class="px-3 py-3">{{ __('Charge') }}</th>
                                            <th class="px-3 py-3">{{ __('Full Price') }}</th>
                                            <th class="px-3 py-3">{{ __('Paid') }}</th>
                                            <th class="px-3 py-3">{{ __('Balance') }}</th>
                                            <th class="px-3 py-3">{{ __('Amount to Pay') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line bg-white">
                                        @foreach ($charges as $index => $charge)
                                            <tr>
                                                <td class="px-3 py-3 text-sm">
                                                    <input type="checkbox" :checked="Number(rows[{{ $index }}].amount) > 0" @change="toggle({{ $index }}, $event.target.checked)" class="rounded border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500" aria-label="{{ __('Pay :charge in full', ['charge' => $charge['label']]) }}">
                                                </td>
                                                <td class="px-3 py-3 text-sm">
                                                    {{ $charge['label'] }}
                                                    @if ($charge['type'] === 'new_service')
                                                        <x-badge color="gray" class="ms-1">{{ __('Not Yet Billed') }}</x-badge>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-3 text-sm">{{ number_format($charge['price'], 2) }}</td>
                                                <td class="px-3 py-3 text-sm">{{ number_format($charge['paid'], 2) }}</td>
                                                <td class="px-3 py-3 text-sm">{{ number_format($charge['balance'], 2) }}</td>
                                                <td class="px-3 py-3 text-sm">
                                                    <input type="hidden" name="allocations[{{ $index }}][type]" value="{{ $charge['type'] }}">
                                                    <input type="hidden" name="allocations[{{ $index }}][id]" value="{{ $charge['id'] }}">
                                                    <input
                                                        type="number"
                                                        name="allocations[{{ $index }}][amount]"
                                                        step="0.01"
                                                        min="0"
                                                        max="{{ $charge['balance'] }}"
                                                        placeholder="0.00"
                                                        x-model="rows[{{ $index }}].amount"
                                                        class="block w-32 border-line focus:border-gold-500 focus:ring-gold-500 rounded-lg shadow-sm"
                                                    >
                                                    <x-input-error class="mt-1" :messages="$errors->get('allocations.'.$index.'.amount')" />
                                                    <x-input-error class="mt-1" :messages="$errors->get('allocations.'.$index.'.id')" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="amount" :value="__('Payment Amount')" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full bg-paper font-bold text-navy-900" :value="old('amount')" x-bind:value="total" readonly required />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('Calculated automatically from the charges ticked above.') }}</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                                    <x-select id="payment_method" name="payment_method" class="mt-1 block w-full" required>
                                        @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                                        @endforeach
                                    </x-select>
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_date" :value="__('Payment Date')" />
                                    <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" :value="old('payment_date', now()->toDateString())" :max="now()->format('Y-m-d')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
                                </div>

                                <div>
                                    <x-input-label for="reference_number" :value="__('Reference Number')" />
                                    <x-text-input id="reference_number" name="reference_number" type="text" class="mt-1 block w-full" :value="old('reference_number')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="notes" :value="__('Notes')" />
                                <x-textarea id="notes" name="notes" rows="3" class="mt-1 block w-full">{{ old('notes') }}</x-textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-line">
                                <x-primary-button>{{ __('Save Payment') }}</x-primary-button>
                                <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
