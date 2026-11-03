<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Correct Payment Allocation') }}
        </h2>
    </x-slot>

    @php
        $pencilIconPath = 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125';
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6 px-4 sm:px-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pencilIconPath }}" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ __('Correct Payment Allocation') }}</h3>
                    <p class="text-sm text-gray-500 truncate">{{ $payment->student->name }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Receipt') }} <span class="font-mono">{{ $payment->receipt_number }}</span> — {{ $payment->student->name }}</p>
                    <p class="text-sm text-gray-500">{{ __('This re-splits the payment\'s existing total; it never changes the amount actually paid.') }}</p>
                </div>

                <x-input-error :messages="$errors->get('allocations')" />

                <form method="post" action="{{ route('payments.correct.update', $payment) }}" class="space-y-6">
                    @csrf
                    @method('put')

                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                    <th class="px-3 py-3">{{ __('Charge') }}</th>
                                    <th class="px-3 py-3">{{ __('Current Amount') }}</th>
                                    <th class="px-3 py-3">{{ __('Corrected Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($payment->allocations as $index => $allocation)
                                    <tr>
                                        <td class="px-3 py-3 text-sm">{{ $allocation->label() }}</td>
                                        <td class="px-3 py-3 text-sm">{{ number_format($allocation->amount, 2) }}</td>
                                        <td class="px-3 py-3 text-sm">
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
                                <tr class="bg-amber-50 font-semibold">
                                    <td class="px-3 py-3 text-sm">{{ __('TOTAL (must stay the same)') }}</td>
                                    <td class="px-3 py-3 text-sm" colspan="2">{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

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
