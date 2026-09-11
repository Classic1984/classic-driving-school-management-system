<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotation :number', ['number' => $quotation->quotation_number]) }}
        </h2>
    </x-slot>

    @php
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $arrowRightIconPath = 'M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3';
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $mapPinIconPath = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
        $globeIconPath = 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418';
        $trashIconPath = 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0';
        $buildingIconPath = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $listIconPath = 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z';
        $coinIconPath = 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $canDelete = $quotation->status !== 'converted';

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
                    <a href="{{ route('corporate-quotations.pdf', $quotation) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-3L12 16.5m0 0 3.75-3.75M12 16.5V3" /></svg>
                        {{ __('Download PDF') }}
                    </a>
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'send-email')" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        {{ __('Send Email') }}
                    </button>
                    <form method="post" action="{{ route('corporate-quotations.whatsapp', $quotation) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                            {{ __('Send WhatsApp') }}
                        </button>
                    </form>
                    @if ($canDelete)
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'delete-quotation')" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trashIconPath }}" /></svg>
                            {{ __('Delete') }}
                        </button>
                    @endif
                    <a href="{{ route('corporate-companies.show', $quotation->corporate_company_id) }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-4 py-2 text-sm font-bold text-amber-400 transition">
                        {{ __('Back to Company') }}
                    </a>
                </div>
            </div>

            @if (session('status') === 'quotation-created')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation created successfully.') }}</p>
            @elseif (session('status') === 'quotation-sent')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation marked as sent.') }}</p>
            @elseif (session('status') === 'quotation-already-converted')
                <p class="print-hidden text-sm font-medium text-amber-600">{{ __('This quotation was already converted to an invoice.') }}</p>
            @elseif (session('status') === 'quotation-emailed')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation emailed successfully.') }}</p>
            @elseif (session('status') === 'quotation-whatsapp-sent')
                <p class="print-hidden text-sm font-medium text-green-600">{{ __('Quotation sent via WhatsApp.') }}</p>
            @elseif (session('status') === 'quotation-whatsapp-not-configured')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('WhatsApp sending isn\'t set up yet. Ask your developer to add the Twilio WhatsApp credentials.') }}</p>
            @elseif (session('status') === 'quotation-whatsapp-no-phone')
                <p class="print-hidden text-sm font-medium text-red-600">
                    {{ __('This company has no phone number on file.') }}
                    <a href="{{ route('corporate-companies.edit', $quotation->corporate_company_id) }}" class="underline hover:no-underline">{{ __('Add one') }}</a>
                    {{ __('and try again.') }}
                </p>
            @elseif (session('status') === 'quotation-whatsapp-failed')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('WhatsApp could not deliver this message. Double-check the phone number is correct and on WhatsApp.') }}</p>
            @elseif (session('status') === 'quotation-already-converted-cannot-delete')
                <p class="print-hidden text-sm font-medium text-red-600">{{ __('This quotation has already been converted to an invoice and cannot be deleted.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 sm:p-10">
                <div class="text-center pb-6 border-b-2 border-amber-400">
                    <x-application-logo class="h-14 w-auto mx-auto mb-2" />
                    <p class="text-lg sm:text-xl font-extrabold uppercase tracking-[0.2em] text-black">{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
                    <h1 class="mt-1 text-5xl sm:text-6xl font-black tracking-tight bg-gradient-to-b from-amber-300 via-amber-600 to-black bg-clip-text text-transparent">{{ __('QUOTATION') }}</h1>
                    @if ($settings->slogan)
                        <p class="italic text-amber-600 text-sm mt-1">{{ $settings->slogan }}</p>
                    @endif
                </div>

                <dl class="mt-4 text-sm space-y-1">
                    <div class="flex gap-2">
                        <dt class="font-bold text-amber-600">{{ __('Quotation No:') }}</dt>
                        <dd class="font-mono text-gray-900">{{ $quotation->quotation_number }}</dd>
                    </div>
                    <div class="flex gap-2">
                        <dt class="font-bold text-amber-600">{{ __('Date:') }}</dt>
                        <dd class="text-gray-900">{{ $quotation->issue_date->format('d F Y') }}</dd>
                    </div>
                    @if ($quotation->valid_until)
                        <div class="flex gap-2">
                            <dt class="font-bold text-amber-600">{{ __('Valid Until:') }}</dt>
                            <dd class="text-gray-900">{{ $quotation->valid_until->format('d F Y') }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div class="rounded-2xl overflow-hidden ring-2 ring-amber-300">
                        <div class="bg-black flex items-center gap-3 px-4 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-400 text-black">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
                            </span>
                            <span class="font-bold text-sm tracking-wide text-amber-400">{{ __('QUOTED TO') }}</span>
                        </div>
                        <div class="bg-white px-4 py-4">
                            <p class="text-lg font-bold text-gray-900">{{ $quotation->company->name }}</p>
                            <p class="text-gray-600 text-sm mt-1">{{ implode(', ', array_filter([$quotation->company->address, $quotation->company->city])) }}</p>
                        </div>
                    </div>
                    @if ($quotation->programme_name || $quotation->duration_label || $quotation->participant_count)
                        <div class="rounded-2xl overflow-hidden ring-2 ring-amber-300">
                            <div class="bg-black flex items-center gap-3 px-4 py-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-400 text-black">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                                </span>
                                <span class="font-bold text-sm tracking-wide text-amber-400">{{ __('TRAINING DETAILS') }}</span>
                            </div>
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
                </div>

                <div class="rounded-2xl overflow-hidden ring-2 ring-amber-300 mt-6">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-black text-amber-400 text-left text-sm font-bold">
                                <th class="px-4 py-2 w-14">{{ __('S/N') }}</th>
                                <th class="px-4 py-2">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-center w-20">{{ __('Qty') }}</th>
                                <th class="px-4 py-2 text-right w-36">{{ __('Unit Price (₦)') }}</th>
                                <th class="px-4 py-2 text-right w-36">{{ __('Amount (₦)') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-amber-50/20 divide-y divide-amber-100">
                            @foreach ($quotation->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $loop->iteration }}</td>
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
                    <div class="flex w-full sm:w-auto rounded-full overflow-hidden ring-2 ring-amber-300">
                        <div class="bg-gradient-to-r from-amber-300 to-amber-500 text-black font-bold text-xs sm:text-sm flex items-center gap-1.5 sm:gap-2 pl-4 pr-3 sm:pl-5 sm:pr-4 py-3 whitespace-nowrap">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $coinIconPath }}" /></svg>
                            {{ __('TOTAL') }}
                        </div>
                        <div class="bg-black text-amber-400 font-black text-lg sm:text-xl flex items-center whitespace-nowrap pl-3 pr-4 sm:pl-4 sm:pr-5 py-3">₦{{ number_format($quotation->total(), 0) }}</div>
                    </div>
                </div>

                @if (! empty($courseCoveragePairs))
                    <div class="rounded-2xl overflow-hidden ring-2 ring-amber-300 mt-6">
                        <div class="bg-black flex items-center gap-3 px-4 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-400 text-black">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $listIconPath }}" /></svg>
                            </span>
                            <span class="font-bold text-sm tracking-wide text-amber-400">{{ __('COURSE COVERAGE') }}</span>
                        </div>
                        <div class="bg-white divide-y divide-amber-100">
                            @foreach ($courseCoveragePairs as $pair)
                                <div class="grid grid-cols-1 sm:grid-cols-2 divide-y sm:divide-y-0 sm:divide-x divide-amber-100 {{ $loop->even ? 'bg-amber-50/60' : '' }}">
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

                @if ($settings->address || $settings->phone || $settings->website)
                    <div class="border-t-2 border-black mt-8 pt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-3 text-sm text-black">
                        @if ($settings->address)
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                                </span>
                                {{ $settings->address }}
                            </div>
                        @endif
                        @if ($settings->phone)
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                                </span>
                                {{ $settings->phone }}
                            </div>
                        @endif
                        @if ($settings->website)
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $globeIconPath }}" /></svg>
                                </span>
                                {{ $settings->website }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="print-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h4 class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-4">{{ __('Activity') }}</h4>
                <x-activity-timeline :logs="$activityLogs" />
            </div>
        </div>
    </div>

    @if ($canDelete)
        <x-modal name="delete-quotation" focusable>
            <form method="post" action="{{ route('corporate-quotations.destroy', $quotation) }}" class="p-6 space-y-4">
                @csrf
                @method('DELETE')
                <h2 class="text-lg font-bold text-gray-900">{{ __('Delete Quotation') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Quotation :number and its line items will be permanently deleted. This cannot be undone.', ['number' => $quotation->quotation_number]) }}</p>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 hover:bg-red-700 px-5 py-2.5 text-sm font-bold text-white transition">
                        {{ __('Confirm Delete') }}
                    </button>
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'delete-quotation')">{{ __('Never Mind') }}</x-secondary-button>
                </div>
            </form>
        </x-modal>
    @endif

    <x-modal name="send-email" focusable>
        <form method="post" action="{{ route('corporate-quotations.email', $quotation) }}" class="p-6 space-y-4">
            @csrf
            <h2 class="text-lg font-bold text-gray-900">{{ __('Send Quotation by Email') }}</h2>
            <p class="text-sm text-gray-500">{{ __('The quotation PDF will be attached automatically.') }}</p>
            <div>
                <x-input-label for="recipient_email" :value="__('Recipient Email')" />
                <x-text-input id="recipient_email" name="recipient_email" type="email" class="block w-full mt-1" :value="old('recipient_email', $quotation->company->email)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('recipient_email')" />
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-2.5 text-sm font-bold text-amber-400 transition">{{ __('Send') }}</button>
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'send-email')">{{ __('Cancel') }}</x-secondary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
