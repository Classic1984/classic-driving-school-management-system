<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Receipt') }}
        </h2>
    </x-slot>

    <style>
        .receipt-header {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden print:shadow-none print:ring-0">
                <div class="receipt-header bg-gray-900 text-white px-8 py-6 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <x-application-logo class="h-14 w-auto" />
                        <div>
                            <p class="font-bold text-lg leading-tight">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                            <p class="text-xs text-amber-400 uppercase tracking-widest mt-0.5">{{ __('Official Payment Receipt') }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] uppercase tracking-widest text-gray-400">{{ __('Receipt No.') }}</p>
                        <p class="font-mono text-sm text-amber-400">{{ $payment->receipt_number }}</p>
                        <x-qr-code :data="$payment->qrCodeSummary()" class="mt-2 inline-block bg-white p-1 rounded [&_svg]:h-16 [&_svg]:w-16" />
                    </div>
                </div>

                <div class="px-8 py-6">
                    <div class="flex items-start justify-between border-b border-gray-200 pb-6 mb-6">
                        <dl class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('Student Name') }}</dt>
                                <dd class="text-gray-900 font-medium">{{ $payment->student->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('Student ID') }}</dt>
                                <dd class="text-gray-900 font-mono">{{ $payment->student->student_id_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('Date') }}</dt>
                                <dd class="text-gray-900">{{ $payment->payment_date->format('j F, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('Payment Method') }}</dt>
                                <dd class="text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
                            </div>
                            @if ($payment->reference_number)
                                <div class="col-span-2">
                                    <dt class="text-xs uppercase tracking-wide text-gray-500">{{ __('Reference Number') }}</dt>
                                    <dd class="text-gray-900">{{ $payment->reference_number }}</dd>
                                </div>
                            @endif
                        </dl>

                        <x-badge :color="match ($payment->status) {
                            'paid' => 'green',
                            'pending' => 'amber',
                            'failed' => 'red',
                            'refunded' => 'blue',
                            default => 'gray',
                        }" class="capitalize shrink-0">{{ $payment->status }}</x-badge>
                    </div>

                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ __('Payment Details') }}</h3>
                    <div class="overflow-x-auto rounded-md ring-1 ring-gray-200 mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="px-4 py-2">{{ __('Service') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Amount Paid') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($payment->allocations as $allocation)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $allocation->label() }}</td>
                                        <td class="px-4 py-2 text-sm text-right font-mono">₦{{ number_format($allocation->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 text-sm">{{ __('TOTAL PAID') }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono text-amber-600">₦{{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">{{ __('Current Balance') }}</h3>
                    <div class="overflow-x-auto rounded-md ring-1 ring-gray-200 mb-8">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($balances as $balance)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">{{ $balance['label'] }} {{ __('Balance') }}</td>
                                        <td class="px-4 py-2 text-sm text-right font-mono">₦{{ number_format($balance['balance'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2 text-sm">{{ __('Total Outstanding') }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-mono">₦{{ number_format($balances->sum('balance'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="flex items-end justify-between border-t border-gray-200 pt-6">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Recorded By') }}: <span class="text-gray-900">{{ $payment->recordedBy?->name ?? __('—') }}</span></p>
                            <p class="text-xs text-gray-400 mt-1">{{ __('Printed on :date', ['date' => now()->format('j F, Y g:i A')]) }}</p>
                        </div>
                        <p class="text-xs italic text-gray-400 text-right">{{ __('"When you say Classic, you say it all."') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4 print:hidden">
                <x-secondary-button type="button" onclick="window.print()">{{ __('Print') }}</x-secondary-button>
                <a href="{{ route('payments.show', $payment) }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to Payment') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
