<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotation :number', ['number' => $quotation->quotation_number]) }}
        </h2>
    </x-slot>

    @php
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $arrowRightIconPath = 'M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3';

        $courseCoveragePairs = array_chunk($quotation->courseCoverageList(), 2);
        $statusMeta = match ($quotation->displayStatus()) {
            'sent' => ['classes' => 'bg-blue-100 text-blue-700', 'label' => 'Sent'],
            'approved' => ['classes' => 'bg-green-100 text-green-700', 'label' => 'Approved'],
            'rejected' => ['classes' => 'bg-red-100 text-red-700', 'label' => 'Rejected'],
            'expired' => ['classes' => 'bg-gray-200 text-gray-700', 'label' => 'Expired'],
            'converted' => ['classes' => 'bg-amber-100 text-amber-700', 'label' => 'Converted'],
            default => ['classes' => 'bg-gray-100 text-gray-600', 'label' => 'Draft'],
        };
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
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['classes'] }}">{{ __($statusMeta['label']) }}</span>
                <div class="flex items-center gap-2">
                    @if ($quotation->status === 'draft')
                        <form method="post" action="{{ route('corporate-quotations.send', $quotation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-blue-300 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition">
                                {{ __('Mark Sent') }}
                            </button>
                        </form>
                    @endif
                    @if ($quotation->status !== 'converted')
                        <form method="post" action="{{ route('corporate-quotations.convert', $quotation) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-4 py-2 text-sm font-bold text-black transition">
                                {{ __('Convert to Invoice') }}
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowRightIconPath }}" /></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('corporate-invoices.show', $quotation->converted_invoice_id) }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-4 py-2 text-sm font-bold text-black transition">
                            {{ __('View Invoice :number', ['number' => $quotation->convertedInvoice?->invoice_number]) }}
                        </a>
                    @endif
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                        {{ __('Print') }}
                    </button>
                    <a href="{{ route('corporate-quotations.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-4 py-2 text-sm font-bold text-amber-400 transition">
                        {{ __('Back to Quotations') }}
                    </a>
                </div>
            </div>

            @if (session('status') === 'quotation-created')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation created successfully.') }}</p>
            @elseif (session('status') === 'quotation-sent')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation marked as sent.') }}</p>
            @elseif (session('status') === 'quotation-already-converted')
                <p class="print-hidden text-sm font-medium text-amber-600">{{ __('This quotation was already converted to an invoice.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 sm:p-10">
                <div class="flex flex-wrap items-start justify-between gap-6 pb-4 border-b-2 border-blue-900">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-blue-900 tracking-tight leading-none">{{ strtoupper($quotation->company->name) }}</h1>
                        <p class="text-gray-500 text-sm mt-1">{{ implode(', ', array_filter([$quotation->company->address, $quotation->company->city])) }}</p>
                    </div>
                    <div class="text-right">
                        <h2 class="text-3xl sm:text-4xl font-black text-blue-900 leading-none">{{ __('QUOTATION') }}</h2>
                        <dl class="mt-2 text-sm">
                            <div class="flex justify-end gap-2">
                                <dt class="font-bold text-gray-700">{{ __('Quotation No:') }}</dt>
                                <dd class="font-mono text-gray-900">{{ $quotation->quotation_number }}</dd>
                            </div>
                            <div class="flex justify-end gap-2">
                                <dt class="font-bold text-gray-700">{{ __('Date:') }}</dt>
                                <dd class="text-gray-900">{{ $quotation->issue_date->format('d F Y') }}</dd>
                            </div>
                            @if ($quotation->valid_until)
                                <div class="flex justify-end gap-2">
                                    <dt class="font-bold text-gray-700">{{ __('Valid Until:') }}</dt>
                                    <dd class="text-gray-900">{{ $quotation->valid_until->format('d F Y') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                @if ($quotation->programme_name || $quotation->duration_label || $quotation->participant_count)
                    <div class="rounded-lg overflow-hidden ring-1 ring-blue-200 mt-6">
                        <div class="bg-blue-900 text-white font-bold text-sm tracking-wide px-4 py-2">{{ __('TRAINING DETAILS') }}</div>
                        <div class="bg-white px-4 py-4 text-sm space-y-1.5">
                            @if ($quotation->programme_name)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Course:') }}</span><span class="text-gray-900">{{ $quotation->programme_name }}</span></div>
                            @endif
                            @if ($quotation->participant_count)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Participant:') }}</span><span class="text-gray-900">{{ $quotation->participant_count }} {{ Str::plural('Driver', $quotation->participant_count) }}</span></div>
                            @endif
                            @if ($quotation->duration_label)
                                <div class="flex gap-2"><span class="font-bold text-gray-700 w-24 shrink-0">{{ __('Duration:') }}</span><span class="text-gray-900">{{ $quotation->duration_label }}</span></div>
                            @endif
                        </div>
                    </div>
                @endif

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
                            @foreach ($quotation->items as $item)
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
                        <div class="flex-1 bg-blue-50 text-blue-900 font-bold text-sm flex items-center px-4 py-3">{{ __('TOTAL') }}</div>
                        <div class="bg-blue-900 text-white font-black text-xl flex items-center px-4 py-3">₦{{ number_format($quotation->total(), 0) }}</div>
                    </div>
                </div>

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

                @if ($quotation->notes)
                    <p class="text-sm text-gray-600 mt-6">{{ $quotation->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
