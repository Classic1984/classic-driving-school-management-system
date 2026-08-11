<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Record Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <form method="get" action="{{ route('payments.record.create') }}" class="flex items-end gap-4">
                    <div class="flex-1">
                        <x-input-label for="student_id" :value="__('Student')" />
                        <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('Select a student') }}</option>
                            @foreach ($students as $availableStudent)
                                <option value="{{ $availableStudent->id }}" @selected($student?->id === $availableStudent->id)>{{ $availableStudent->name }} ({{ $availableStudent->student_id_number }})</option>
                            @endforeach
                        </select>
                    </div>

                    <x-secondary-button type="submit">{{ __('Load Charges') }}</x-secondary-button>
                </form>
            </div>

            @if ($student)
                <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                    <h3 class="text-lg font-semibold mb-4">{{ $student->name }} ({{ $student->student_id_number }})</h3>

                    @if ($charges->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('This student has no outstanding charges.') }}</p>
                    @else
                        <form method="post" action="{{ route('payments.record.store') }}" class="space-y-6">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">

                            <x-input-error class="mb-2" :messages="$errors->get('allocations')" />

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <th class="px-2 py-1">{{ __('Charge') }}</th>
                                            <th class="px-2 py-1">{{ __('Full Price') }}</th>
                                            <th class="px-2 py-1">{{ __('Paid') }}</th>
                                            <th class="px-2 py-1">{{ __('Balance') }}</th>
                                            <th class="px-2 py-1">{{ __('Amount to Pay') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($charges as $index => $charge)
                                            <tr>
                                                <td class="px-2 py-1 text-sm">
                                                    {{ $charge['label'] }}
                                                    @if ($charge['type'] === 'new_service')
                                                        <x-badge color="gray" class="ms-1">{{ __('Not Yet Billed') }}</x-badge>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-1 text-sm">{{ number_format($charge['price'], 2) }}</td>
                                                <td class="px-2 py-1 text-sm">{{ number_format($charge['paid'], 2) }}</td>
                                                <td class="px-2 py-1 text-sm">{{ number_format($charge['balance'], 2) }}</td>
                                                <td class="px-2 py-1 text-sm">
                                                    <input type="hidden" name="allocations[{{ $index }}][type]" value="{{ $charge['type'] }}">
                                                    <input type="hidden" name="allocations[{{ $index }}][id]" value="{{ $charge['id'] }}">
                                                    <input
                                                        type="number"
                                                        name="allocations[{{ $index }}][amount]"
                                                        step="0.01"
                                                        min="0"
                                                        max="{{ $charge['balance'] }}"
                                                        placeholder="0.00"
                                                        value="{{ old("allocations.{$index}.amount") }}"
                                                        class="block w-32 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm"
                                                    >
                                                    <x-input-error class="mt-1" :messages="$errors->get("allocations.{$index}.amount")" />
                                                    <x-input-error class="mt-1" :messages="$errors->get("allocations.{$index}.id")" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="amount" :value="__('Payment Amount')" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                                    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                                </div>

                                <div>
                                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                                    <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                        @foreach (['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank Transfer', 'mobile_money' => 'Mobile Money'] as $value => $label)
                                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ __($label) }}</option>
                                        @endforeach
                                    </select>
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
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                            </div>

                            <div class="flex items-center gap-4">
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
