<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoices') }}
        </h2>
    </x-slot>

    @php
        $receiptIconPath = 'M9 14.25 6.75 12l2.25-2.25M15 9.75l2.25 2.25-2.25 2.25M3.375 21h17.25c.621 0 1.125-.504 1.125-1.125V4.125C21.75 3.504 21.246 3 20.625 3H3.375C2.754 3 2.25 3.504 2.25 4.125v15.75c0 .621.504 1.125 1.125 1.125Z';
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $statusColors = [
            'paid' => 'bg-green-100 text-green-700',
            'sent' => 'bg-blue-100 text-blue-700',
            'overdue' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-200 text-gray-700',
            'pending' => 'bg-amber-100 text-amber-700',
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('Invoices') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('Bills sent to corporate clients') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('corporate-invoices.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-5 py-2.5 text-sm font-bold text-black transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                            {{ __('Create Invoice') }}
                        </a>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    @if ($invoices->isEmpty())
                        <div class="flex flex-col items-center gap-2 py-10">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                            </span>
                            <p class="text-sm text-gray-500">{{ __('No invoices yet.') }}</p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-black">
                                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                            <th class="px-4 py-3">{{ __('Invoice') }}</th>
                                            <th class="px-4 py-3">{{ __('Company') }}</th>
                                            <th class="px-4 py-3">{{ __('Invoice Date') }}</th>
                                            <th class="px-4 py-3">{{ __('Due Date') }}</th>
                                            <th class="px-4 py-3">{{ __('Total') }}</th>
                                            <th class="px-4 py-3">{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($invoices as $invoice)
                                            @php
                                                $total = $invoice->items->sum(fn ($item) => $item->amount());
                                                $displayStatus = $invoice->displayStatus();
                                            @endphp
                                            <tr class="hover:bg-amber-50/40 transition">
                                                <td class="px-4 py-3 text-sm">
                                                    <a href="{{ route('corporate-invoices.show', $invoice) }}" class="font-mono font-semibold text-amber-600 hover:underline">{{ $invoice->invoice_number }}</a>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $invoice->company->name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $invoice->invoice_date->format('M j, Y') }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $invoice->due_date->format('M j, Y') }}</td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">₦{{ number_format($total, 0) }}</td>
                                                <td class="px-4 py-3">
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$displayStatus] ?? $statusColors['pending'] }}">{{ __(ucfirst($displayStatus)) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
