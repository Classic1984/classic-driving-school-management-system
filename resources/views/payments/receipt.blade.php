<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Receipt') }}
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl p-8 print:shadow-none print:ring-0">
                <div class="flex items-start justify-between border-b border-gray-200 pb-6 mb-6">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-14 w-auto" />
                        <div>
                            <p class="font-bold text-lg">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                            <p class="text-sm text-gray-500 uppercase tracking-widest">{{ __('Payment Receipt') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-widest text-gray-500">{{ __('Receipt No.') }}</p>
                        <p class="font-mono text-sm">{{ $payment->receipt_number }}</p>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-y-2 mb-6 text-sm">
                    <dt class="text-gray-500">{{ __('Student Name') }}</dt>
                    <dd class="text-gray-900">{{ $payment->student->name }}</dd>
                    <dt class="text-gray-500">{{ __('Student ID') }}</dt>
                    <dd class="text-gray-900 font-mono">{{ $payment->student->student_id_number }}</dd>
                    <dt class="text-gray-500">{{ __('Date') }}</dt>
                    <dd class="text-gray-900">{{ $payment->payment_date->format('j F, Y') }}</dd>
                    <dt class="text-gray-500">{{ __('Payment Method') }}</dt>
                    <dd class="text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
                    @if ($payment->reference_number)
                        <dt class="text-gray-500">{{ __('Reference Number') }}</dt>
                        <dd class="text-gray-900">{{ $payment->reference_number }}</dd>
                    @endif
                </dl>

                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Payment Details') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-2 py-1">{{ __('Service') }}</th>
                            <th class="px-2 py-1 text-right">{{ __('Amount Paid') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($payment->allocations as $allocation)
                            <tr>
                                <td class="px-2 py-1 text-sm">{{ $allocation->label() }}</td>
                                <td class="px-2 py-1 text-sm text-right">₦{{ number_format($allocation->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 font-semibold">
                            <td class="px-2 py-1 text-sm">{{ __('TOTAL PAID') }}</td>
                            <td class="px-2 py-1 text-sm text-right">₦{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('Current Balance') }}</h3>
                <table class="min-w-full divide-y divide-gray-200 mb-2">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($balances as $balance)
                            <tr>
                                <td class="px-2 py-1 text-sm">{{ $balance['label'] }} {{ __('Balance') }}</td>
                                <td class="px-2 py-1 text-sm text-right">₦{{ number_format($balance['balance'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 font-semibold">
                            <td class="px-2 py-1 text-sm">{{ __('Total Outstanding') }}</td>
                            <td class="px-2 py-1 text-sm text-right">₦{{ number_format($balances->sum('balance'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <p class="text-sm text-gray-500 mt-6">{{ __('Recorded By') }}: {{ $payment->recordedBy?->name ?? __('—') }}</p>
            </div>

            <div class="mt-6 flex items-center gap-4 print:hidden">
                <x-secondary-button type="button" onclick="window.print()">{{ __('Print') }}</x-secondary-button>
                <a href="{{ route('payments.show', $payment) }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to Payment') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
