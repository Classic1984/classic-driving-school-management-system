<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Correct Payment Allocation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Receipt') }} <span class="font-mono">{{ $payment->receipt_number }}</span> — {{ $payment->student->name }}</p>
                    <p class="text-sm text-gray-500">{{ __('This re-splits the payment\'s existing total; it never changes the amount actually paid.') }}</p>
                </div>

                <x-input-error :messages="$errors->get('allocations')" />

                <form method="post" action="{{ route('payments.correct.update', $payment) }}" class="space-y-6">
                    @csrf
                    @method('put')

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-2 py-1">{{ __('Charge') }}</th>
                                <th class="px-2 py-1">{{ __('Current Amount') }}</th>
                                <th class="px-2 py-1">{{ __('Corrected Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($payment->allocations as $index => $allocation)
                                <tr>
                                    <td class="px-2 py-1 text-sm">{{ $allocation->label() }}</td>
                                    <td class="px-2 py-1 text-sm">{{ number_format($allocation->amount, 2) }}</td>
                                    <td class="px-2 py-1 text-sm">
                                        <input type="hidden" name="allocations[{{ $index }}][id]" value="{{ $allocation->id }}">
                                        <input
                                            type="number"
                                            name="allocations[{{ $index }}][amount]"
                                            step="0.01"
                                            min="0.01"
                                            value="{{ old("allocations.{$index}.amount", $allocation->amount) }}"
                                            class="block w-32 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm"
                                            required
                                        >
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300 font-semibold">
                                <td class="px-2 py-1 text-sm">{{ __('TOTAL (must stay the same)') }}</td>
                                <td class="px-2 py-1 text-sm" colspan="2">{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div>
                        <x-input-label for="reason" :value="__('Reason for Correction')" />
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save Correction') }}</x-primary-button>
                        <a href="{{ route('payments.show', $payment) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
