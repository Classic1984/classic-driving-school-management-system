<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receipt :number', ['number' => $payment->receipt_number]) }}
        </h2>
    </x-slot>

    @php
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $checkCircleIconPath = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
    @endphp

    <style>
        @media print {
            .print-hidden { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="print-hidden flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('corporate-invoices.show', $payment->invoice) }}" class="text-sm font-semibold text-amber-600 hover:underline">
                    ← {{ __('Back to Invoice :number', ['number' => $payment->invoice->invoice_number]) }}
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                        {{ __('Print') }}
                    </button>
                    <a href="{{ route('corporate-payments.receipt.pdf', $payment) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-3L12 16.5m0 0 3.75-3.75M12 16.5V3" /></svg>
                        {{ __('Download PDF') }}
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 sm:p-10">
                <div class="text-center pb-4 border-b-2 border-blue-900">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600 mb-2">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkCircleIconPath }}" /></svg>
                    </span>
                    <h1 class="text-2xl font-black text-blue-900 tracking-wide">{{ __('PAYMENT RECEIPT') }}</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
                </div>

                <dl class="mt-6 divide-y divide-gray-100 text-sm">
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Received From') }}</dt>
                        <dd class="font-bold text-gray-900">{{ $payment->invoice->company->name }}</dd>
                    </div>
                    @if ($payment->invoice->programme_name)
                        <div class="flex justify-between py-2.5">
                            <dt class="font-semibold text-gray-500">{{ __('Programme') }}</dt>
                            <dd class="text-gray-900">{{ $payment->invoice->programme_name }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Invoice No.') }}</dt>
                        <dd class="font-mono text-gray-900">{{ $payment->invoice->invoice_number }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Amount Paid') }}</dt>
                        <dd class="text-xl font-black text-blue-900">₦{{ number_format((float) $payment->amount, 0) }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Payment Method') }}</dt>
                        <dd class="text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
                    </div>
                    @if ($payment->transaction_reference)
                        <div class="flex justify-between py-2.5">
                            <dt class="font-semibold text-gray-500">{{ __('Transaction Reference') }}</dt>
                            <dd class="text-gray-900">{{ $payment->transaction_reference }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Payment Date') }}</dt>
                        <dd class="text-gray-900">{{ $payment->payment_date->format('d F Y') }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Status') }}</dt>
                        <dd><span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-2.5 py-1 text-xs font-bold">{{ __('PAID') }}</span></dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="font-semibold text-gray-500">{{ __('Receipt No.') }}</dt>
                        <dd class="font-mono text-gray-900">{{ $payment->receipt_number }}</dd>
                    </div>
                </dl>

                @if ($payment->notes)
                    <p class="mt-4 text-sm text-gray-500">{{ $payment->notes }}</p>
                @endif

                <p class="mt-8 text-center text-xs text-gray-400">{{ __('Thank you for choosing :company.', ['company' => $settings->company_name ?: 'Classic Driving School']) }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
