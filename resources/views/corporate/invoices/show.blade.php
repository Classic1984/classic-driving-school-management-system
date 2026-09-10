<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice :number', ['number' => $invoice->invoice_number]) }}
        </h2>
    </x-slot>

    @php
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $mapPinIconPath = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';

        $courseCoverage = $invoice->courseCoverageList();
        $courseCoveragePairs = array_chunk($courseCoverage, 2);
        $paymentTerms = $settings->paymentTermsList();
        $statusMeta = match ($invoice->displayStatus()) {
            'paid' => ['classes' => 'bg-green-100 text-green-700', 'label' => 'Paid'],
            'sent' => ['classes' => 'bg-blue-100 text-blue-700', 'label' => 'Sent'],
            'overdue' => ['classes' => 'bg-red-100 text-red-700', 'label' => 'Overdue'],
            'cancelled' => ['classes' => 'bg-gray-200 text-gray-700', 'label' => 'Cancelled'],
            default => ['classes' => 'bg-amber-100 text-amber-700', 'label' => 'Pending'],
        };
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $receiptIconPath = 'M9 14.25 6.75 12l2.25-2.25M15 9.75l2.25 2.25-2.25 2.25M3.375 21h17.25c.621 0 1.125-.504 1.125-1.125V4.125C21.75 3.504 21.246 3 20.625 3H3.375C2.754 3 2.25 3.504 2.25 4.125v15.75c0 .621.504 1.125 1.125 1.125Z';
        $paperAirplaneIconPath = 'M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5';
        $xCircleIconPath = 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $canRecordPayment = ! in_array($invoice->status, ['paid', 'cancelled'], true);
        $canSend = $invoice->status === 'pending';
        $canCancel = ! in_array($invoice->status, ['paid', 'cancelled'], true);
    @endphp

    <style>
        @media print {
            .print-hidden { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="print-hidden flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['classes'] }}">{{ __($statusMeta['label']) }}</span>
                    <span class="text-sm text-gray-500">{{ __('Balance:') }} ₦{{ number_format($invoice->balance(), 0) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @if ($canSend)
                        <form method="post" action="{{ route('corporate-invoices.send', $invoice) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $paperAirplaneIconPath }}" /></svg>
                                {{ __('Mark Sent') }}
                            </button>
                        </form>
                    @endif
                    @if ($canRecordPayment)
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'record-payment')" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-4 py-2 text-sm font-bold text-black transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                            {{ __('Record Payment') }}
                        </button>
                    @endif
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                        {{ __('Print') }}
                    </button>
                    <a href="{{ route('corporate-invoices.pdf', $invoice) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-3L12 16.5m0 0 3.75-3.75M12 16.5V3" /></svg>
                        {{ __('Download PDF') }}
                    </a>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'send-email')" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        {{ __('Send Email') }}
                    </button>
                    <form method="post" action="{{ route('corporate-invoices.whatsapp', $invoice) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            {{ __('Send WhatsApp') }}
                        </button>
                    </form>
                    @if ($canCancel)
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'cancel-invoice')" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $xCircleIconPath }}" /></svg>
                            {{ __('Cancel Invoice') }}
                        </button>
                    @endif
                    <a href="{{ route('corporate-invoices.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-4 py-2 text-sm font-bold text-amber-400 transition">
                        {{ __('Back to Invoices') }}
                    </a>
                </div>
            </div>

            @if (session('status') === 'invoice-created')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Invoice created successfully.') }}</p>
            @elseif (session('status') === 'payment-recorded')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Payment recorded successfully.') }}</p>
            @elseif (session('status') === 'invoice-sent')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Invoice marked as sent.') }}</p>
            @elseif (session('status') === 'invoice-cancelled')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Invoice cancelled.') }}</p>
            @elseif (session('status') === 'invoice-cannot-send')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('This invoice can no longer be marked sent.') }}</p>
            @elseif (session('status') === 'invoice-cannot-cancel')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('A fully paid invoice cannot be cancelled.') }}</p>
            @elseif (session('status') === 'invoice-emailed')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Invoice emailed successfully.') }}</p>
            @elseif (session('status') === 'invoice-whatsapp-sent')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Invoice sent via WhatsApp.') }}</p>
            @elseif (session('status') === 'invoice-whatsapp-failed')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('Could not send the invoice via WhatsApp. Check the company phone number and WhatsApp settings.') }}</p>
            @endif

            @if ($invoice->status === 'cancelled' && $invoice->cancellation_reason)
                <div class="print-hidden bg-red-50 ring-1 ring-red-100 rounded-xl p-4">
                    <p class="text-sm font-semibold text-red-800">{{ __('Cancelled:') }} {{ $invoice->cancellation_reason }}</p>
                </div>
            @endif

            @if ($invoice->payments->isNotEmpty())
                <div class="print-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-3">{{ __('Payments') }}</h4>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($invoice->payments as $payment)
                            <li class="flex flex-wrap items-center justify-between gap-2 py-2.5 text-sm">
                                <div>
                                    <span class="font-bold text-gray-900">₦{{ number_format((float) $payment->amount, 0) }}</span>
                                    <span class="text-gray-500">— {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} · {{ $payment->payment_date->format('M j, Y') }}</span>
                                </div>
                                <a href="{{ route('corporate-payments.receipt', $payment) }}" class="inline-flex items-center gap-1.5 text-amber-600 hover:underline font-semibold">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                                    {{ __('View Receipt') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 sm:p-10">
                <div class="flex flex-wrap items-start justify-between gap-6 pb-4 border-b-2 border-blue-900">
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-black text-blue-900 tracking-tight leading-none">{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</h1>
                        @if ($settings->tagline)
                            <p class="text-gray-500 mt-1">{{ $settings->tagline }}</p>
                        @endif
                        @if ($settings->slogan)
                            <p class="italic text-blue-700 text-sm mt-1">{{ $settings->slogan }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <h2 class="text-3xl sm:text-5xl font-black text-blue-900 leading-none">{{ __('INVOICE') }}</h2>
                        <dl class="mt-2 text-sm">
                            <div class="flex justify-end gap-2">
                                <dt class="font-bold text-gray-700">{{ __('Invoice No:') }}</dt>
                                <dd class="font-mono text-gray-900">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div class="flex justify-end gap-2">
                                <dt class="font-bold text-gray-700">{{ __('Date:') }}</dt>
                                <dd class="text-gray-900">{{ $invoice->invoice_date->format('d F Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div class="rounded-lg overflow-hidden ring-1 ring-blue-200">
                        <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('BILL TO') }}</div>
                        <div class="bg-white px-4 py-4">
                            <p class="text-lg font-bold text-gray-900">{{ $invoice->company->name }}</p>
                            <p class="text-gray-600 text-sm mt-1">{{ implode(', ', array_filter([$invoice->company->address, $invoice->company->city])) }}</p>
                        </div>
                    </div>
                    <div class="rounded-lg overflow-hidden ring-1 ring-blue-200">
                        <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('TRAINING DETAILS') }}</div>
                        <div class="bg-white px-4 py-4 text-sm space-y-1.5">
                            @if ($invoice->programme_name)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Course:') }}</span><span class="text-gray-900">{{ $invoice->programme_name }}</span></div>
                            @endif
                            @if ($invoice->participant_count)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Participant:') }}</span><span class="text-gray-900">{{ $invoice->participant_count }} {{ Str::plural('Driver', $invoice->participant_count) }}</span></div>
                            @endif
                            @if ($invoice->duration_label)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Duration:') }}</span><span class="text-gray-900">{{ $invoice->duration_label }}</span></div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-lg overflow-hidden ring-1 ring-blue-200 mt-6">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-blue-900 text-white text-left text-sm font-bold">
                                <th class="px-4 py-2">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-center w-20">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-right w-36">{{ __('Unit Price (₦)') }}</th>
                                <th class="px-4 py-2 text-right w-36">{{ __('Amount (₦)') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-blue-100">
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-center">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 text-right">{{ number_format((float) $item->unit_price, 0) }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">{{ number_format($item->amount(), 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-4">
                    <div class="flex w-full sm:w-1/2 rounded-lg overflow-hidden ring-1 ring-blue-200">
                        <div class="flex-1 bg-blue-50 text-blue-900 font-bold text-sm flex items-center px-4 py-3">{{ __('TOTAL AMOUNT DUE') }}</div>
                        <div class="bg-blue-900 text-white font-black text-xl flex items-center px-4 py-3">₦{{ number_format($invoice->total(), 0) }}</div>
                    </div>
                </div>

                <p class="text-sm text-gray-700 mt-3"><span class="font-bold">{{ __('Amount in Words:') }}</span> {{ $invoice->totalInWords() }}</p>

                @if (! empty($courseCoveragePairs))
                    <div class="rounded-lg overflow-hidden ring-1 ring-blue-200 mt-6">
                        <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('COURSE COVERAGE') }}</div>
                        <div class="bg-white divide-y divide-blue-100">
                            @foreach ($courseCoveragePairs as $pair)
                                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-blue-100 {{ $loop->even ? 'bg-blue-50/60' : '' }}">
                                    @foreach ($pair as $topic)
                                        <div class="px-4 py-2 text-sm text-gray-800">{{ $topic }}</div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    @if (! empty($paymentTerms))
                        <div class="rounded-lg overflow-hidden ring-1 ring-blue-200">
                            <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('PAYMENT TERMS') }}</div>
                            <div class="bg-white px-4 py-4">
                                <ul class="text-sm text-gray-700 space-y-1.5 list-disc list-inside">
                                    @foreach ($paymentTerms as $term)
                                        <li>{{ $term }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <div class="rounded-lg overflow-hidden ring-1 ring-blue-200">
                        <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('PAYMENT DETAILS') }}</div>
                        <div class="bg-white px-4 py-4 text-sm space-y-1.5">
                            @if ($settings->bank_name)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-32 shrink-0">{{ __('Bank Name:') }}</span><span class="text-gray-900">{{ $settings->bank_name }}</span></div>
                            @endif
                            @if ($settings->bank_account_name)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-32 shrink-0">{{ __('Account Name:') }}</span><span class="text-gray-900">{{ $settings->bank_account_name }}</span></div>
                            @endif
                            @if ($settings->bank_account_number)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-32 shrink-0">{{ __('Account Number:') }}</span><span class="text-gray-900">{{ $settings->bank_account_number }}</span></div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-lg overflow-hidden ring-1 ring-blue-200 mt-6 max-w-sm">
                    <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __("DIRECTOR'S SIGNATURE") }}</div>
                    <div class="bg-white px-4 py-4 text-center">
                        @if ($settings->signature_path)
                            <img src="{{ Storage::disk('public')->url($settings->signature_path) }}" alt="{{ __('Signature') }}" class="h-16 mx-auto object-contain">
                        @else
                            <div class="h-16"></div>
                        @endif
                        <div class="border-t border-gray-300 mt-1 pt-1">
                            <p class="text-sm font-semibold text-gray-900">{{ __('Director') }}</p>
                            <p class="text-xs text-gray-500">{{ $settings->company_name ?: __('Classic Driving School') }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t-2 border-blue-900 mt-8 pt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-blue-900">
                    @if ($settings->phone)
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                            {{ $settings->phone }}
                        </span>
                    @endif
                    @if ($settings->slogan)
                        <span class="italic">{{ $settings->slogan }}</span>
                    @endif
                    @if ($settings->address)
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                            {{ $settings->address }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if ($canRecordPayment)
            <x-modal name="record-payment" focusable>
                <form method="post" action="{{ route('corporate-invoices.payments.store', $invoice) }}" class="p-6 space-y-4">
                    @csrf
                    <h2 class="text-lg font-bold text-gray-900">{{ __('Record Payment') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Invoice :number — balance ₦:balance', ['number' => $invoice->invoice_number, 'balance' => number_format($invoice->balance(), 0)]) }}</p>

                    <div>
                        <x-input-label for="amount" :value="__('Amount Paid (₦)')" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="block w-full mt-1" :value="old('amount', $invoice->balance())" required autofocus />
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
                        <x-text-input id="payment_date" name="payment_date" type="date" class="block w-full mt-1" :value="old('payment_date', now()->format('Y-m-d'))" required />
                        <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
                    </div>

                    <div>
                        <x-input-label for="transaction_reference" :value="__('Transaction Reference (optional)')" />
                        <x-text-input id="transaction_reference" name="transaction_reference" type="text" class="block w-full mt-1" :value="old('transaction_reference')" />
                        <x-input-error class="mt-2" :messages="$errors->get('transaction_reference')" />
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-2.5 text-sm font-bold text-amber-400 transition">
                            {{ __('Confirm Payment') }}
                        </button>
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'record-payment')">{{ __('Cancel') }}</x-secondary-button>
                    </div>
                </form>
            </x-modal>
        @endif

        @if ($canCancel)
            <x-modal name="cancel-invoice" focusable>
                <form method="post" action="{{ route('corporate-invoices.cancel', $invoice) }}" class="p-6 space-y-4">
                    @csrf
                    <h2 class="text-lg font-bold text-gray-900">{{ __('Cancel Invoice') }}</h2>
                    <p class="text-sm text-gray-500">{{ __('Invoice :number will be marked cancelled. This cannot be undone.', ['number' => $invoice->invoice_number]) }}</p>

                    <div>
                        <x-input-label for="cancellation_reason" :value="__('Reason')" />
                        <x-text-input id="cancellation_reason" name="cancellation_reason" type="text" class="block w-full mt-1" placeholder="{{ __('e.g. Client cancelled the training') }}" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('cancellation_reason')" />
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 hover:bg-red-700 px-5 py-2.5 text-sm font-bold text-white transition">
                            {{ __('Confirm Cancellation') }}
                        </button>
                        <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'cancel-invoice')">{{ __('Never Mind') }}</x-secondary-button>
                    </div>
                </form>
            </x-modal>
        @endif

        <x-modal name="send-email" focusable>
            <form method="post" action="{{ route('corporate-invoices.email', $invoice) }}" class="p-6 space-y-4">
                @csrf
                <h2 class="text-lg font-bold text-gray-900">{{ __('Send Invoice by Email') }}</h2>
                <p class="text-sm text-gray-500">{{ __('The invoice PDF will be attached automatically.') }}</p>

                <div>
                    <x-input-label for="recipient_email" :value="__('Recipient Email')" />
                    <x-text-input id="recipient_email" name="recipient_email" type="email" class="block w-full mt-1" :value="old('recipient_email', $invoice->company->email)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('recipient_email')" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-2.5 text-sm font-bold text-amber-400 transition">
                        {{ __('Send') }}
                    </button>
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'send-email')">{{ __('Cancel') }}</x-secondary-button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
