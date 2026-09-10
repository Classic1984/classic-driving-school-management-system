<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $company->name }}
        </h2>
    </x-slot>

    @php
        $buildingIconPath = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
        $mapPinIconPath = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
        $userIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $receiptIconPath = 'M9 14.25 6.75 12l2.25-2.25M15 9.75l2.25 2.25-2.25 2.25M3.375 21h17.25c.621 0 1.125-.504 1.125-1.125V4.125C21.75 3.504 21.246 3 20.625 3H3.375C2.754 3 2.25 3.504 2.25 4.125v15.75c0 .621.504 1.125 1.125 1.125Z';
    @endphp

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $company->name }}</h3>
                                <p class="text-sm text-gray-400 font-mono">{{ $company->company_reference }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('corporate-quotations.create', ['corporate_company_id' => $company->id]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                {{ __('New Quotation') }}
                            </a>
                            <a href="{{ route('corporate-invoices.create', ['corporate_company_id' => $company->id]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                {{ __('New Invoice') }}
                            </a>
                            <a href="{{ route('corporate-companies.edit', $company) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                {{ __('Edit') }}
                            </a>
                            <a href="{{ route('corporate-companies.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-4 py-2 text-sm font-bold text-black transition">
                                {{ __('Back to Companies') }}
                            </a>
                        </div>
                    </div>

                    <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
                        <div class="flex items-start gap-2 rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
                            <svg class="h-4 w-4 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-400">{{ __('Location') }}</p>
                                <p class="text-sm font-semibold text-white">{{ implode(', ', array_filter([$company->address, $company->city])) ?: '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
                            <svg class="h-4 w-4 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $userIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-400">{{ __('Contact Person') }}</p>
                                <p class="text-sm font-semibold text-white">{{ $company->contact_person ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
                            <svg class="h-4 w-4 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-400">{{ __('Phone') }}</p>
                                <p class="text-sm font-semibold text-white">{{ $company->phone ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
                            <svg class="h-4 w-4 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-400">{{ __('Email') }}</p>
                                <p class="text-sm font-semibold text-white">{{ $company->email ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($company->notes)
                        <div class="relative mt-4 rounded-xl bg-white/5 ring-1 ring-white/10 p-4">
                            <p class="text-xs text-gray-400">{{ __('Notes') }}</p>
                            <p class="text-sm text-gray-200 mt-1">{{ $company->notes }}</p>
                        </div>
                    @endif
                </div>

                @php
                    $statusColor = fn (string $status) => match ($status) {
                        'paid', 'approved' => 'green',
                        'rejected', 'expired', 'overdue', 'cancelled' => 'red',
                        'sent', 'converted' => 'blue',
                        default => 'amber',
                    };
                    $quotationStatuses = ['draft', 'sent', 'approved', 'rejected', 'expired', 'converted'];
                    $invoiceStatuses = ['pending', 'sent', 'paid', 'overdue', 'cancelled'];
                @endphp

                <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                            {{ __('Quotations') }}
                        </h4>

                        @if ($company->quotations->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <a href="{{ route('corporate-companies.show', array_filter(['corporate_company' => $company->id, 'invoice_status' => $invoiceStatus])) }}#quotations" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $quotationStatus ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-black text-amber-400' }}">{{ __('All') }}</a>
                                @foreach ($quotationStatuses as $status)
                                    <a href="{{ route('corporate-companies.show', array_filter(['corporate_company' => $company->id, 'quotation_status' => $status, 'invoice_status' => $invoiceStatus])) }}#quotations" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $quotationStatus === $status ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ __(ucfirst($status)) }}</a>
                                @endforeach
                            </div>
                        @endif

                        @if ($filteredQuotations->isEmpty())
                            <p id="quotations" class="mt-3 text-sm text-gray-500">{{ $quotationStatus ? __('No quotations with this status.') : __('No quotations yet.') }}</p>
                        @else
                            <ul id="quotations" class="mt-3 divide-y divide-gray-100 ring-1 ring-gray-200 rounded-lg overflow-hidden">
                                @foreach ($filteredQuotations as $quotation)
                                    <li>
                                        <a href="{{ route('corporate-quotations.show', $quotation) }}" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-amber-50/40 transition">
                                            <span class="font-mono text-gray-700">{{ $quotation->quotation_number }}</span>
                                            <x-badge :color="$statusColor($quotation->displayStatus())">{{ __(ucfirst($quotation->displayStatus())) }}</x-badge>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div>
                        <h4 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                            {{ __('Invoices') }}
                        </h4>

                        @if ($company->invoices->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <a href="{{ route('corporate-companies.show', array_filter(['corporate_company' => $company->id, 'quotation_status' => $quotationStatus])) }}#invoices" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $invoiceStatus ? 'bg-gray-100 text-gray-600 hover:bg-gray-200' : 'bg-black text-amber-400' }}">{{ __('All') }}</a>
                                @foreach ($invoiceStatuses as $status)
                                    <a href="{{ route('corporate-companies.show', array_filter(['corporate_company' => $company->id, 'invoice_status' => $status, 'quotation_status' => $quotationStatus])) }}#invoices" class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $invoiceStatus === $status ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ __(ucfirst($status)) }}</a>
                                @endforeach
                            </div>
                        @endif

                        @if ($filteredInvoices->isEmpty())
                            <p id="invoices" class="mt-3 text-sm text-gray-500">{{ $invoiceStatus ? __('No invoices with this status.') : __('No invoices yet.') }}</p>
                        @else
                            <ul id="invoices" class="mt-3 divide-y divide-gray-100 ring-1 ring-gray-200 rounded-lg overflow-hidden">
                                @foreach ($filteredInvoices as $invoice)
                                    <li>
                                        <a href="{{ route('corporate-invoices.show', $invoice) }}" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-amber-50/40 transition">
                                            <span class="font-mono text-gray-700">{{ $invoice->invoice_number }}</span>
                                            <x-badge :color="$statusColor($invoice->displayStatus())">{{ __(ucfirst($invoice->displayStatus())) }}</x-badge>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
