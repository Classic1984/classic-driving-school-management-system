<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Reports') }}
        </h2>
    </x-slot>

    @php
        $chartBarIconPath = 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $bankIconPath = 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-18 0h18M3 9v1.5a.75.75 0 0 0 .75.75h16.5a.75.75 0 0 0 .75-.75V9';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $globeIconPath = 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c-1.657 0-3-4.03-3-9s1.343-9 3-9 3 4.03 3 9-1.343 9-3 9ZM3.75 9h16.5M3.75 15h16.5';
        $receiptIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $walletIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-end gap-2 print:hidden">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                    {{ __('Print') }}
                </button>
                <a href="{{ route('payment-reports.export') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    {{ __('Export Outstanding CSV') }}
                </a>
                <a href="{{ route('payment-reports.export-pdf', ['date' => $date]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                    {{ __('Download PDF') }}
                </a>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-black text-amber-400">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $walletIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ __('Daily Payment Report') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('Payments received on the selected date') }}</p>
                            </div>
                        </div>
                        <form method="get" action="{{ route('payment-reports.index') }}" class="print:hidden">
                            <input type="date" name="date" value="{{ $date }}" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm text-sm" onchange="this.form.submit()" max="{{ now()->format('Y-m-d') }}">
                        </form>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="relative overflow-hidden rounded-xl bg-black p-4">
                            <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chartBarIconPath }}" /></svg>
                            <p class="relative text-xs uppercase tracking-wider text-amber-400/80">{{ __('Payments') }}</p>
                            <p class="relative text-2xl font-extrabold text-amber-400 mt-1">{{ $daily['count'] }}</p>
                        </div>
                        <div class="rounded-xl bg-green-50 p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-500/10 text-green-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                            </span>
                            <p class="text-xs uppercase tracking-wider text-green-700 mt-2">{{ __('Cash') }}</p>
                            <p class="text-lg font-bold text-green-900 mt-0.5">₦{{ number_format($daily['cash'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bankIconPath }}" /></svg>
                            </span>
                            <p class="text-xs uppercase tracking-wider text-blue-700 mt-2">{{ __('Transfers') }}</p>
                            <p class="text-lg font-bold text-blue-900 mt-0.5">₦{{ number_format($daily['bank_transfer'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-indigo-50 p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                            </span>
                            <p class="text-xs uppercase tracking-wider text-indigo-700 mt-2">{{ __('POS') }}</p>
                            <p class="text-lg font-bold text-indigo-900 mt-0.5">₦{{ number_format($daily['card'], 2) }}</p>
                        </div>
                        <div class="rounded-xl bg-purple-50 p-4">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-500/10 text-purple-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $globeIconPath }}" /></svg>
                            </span>
                            <p class="text-xs uppercase tracking-wider text-purple-700 mt-2">{{ __('Online') }}</p>
                            <p class="text-lg font-bold text-purple-900 mt-0.5">₦{{ number_format($daily['mobile_money'], 2) }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Services Paid For') }}</p>
                        @if ($daily['services']->isEmpty())
                            <p class="text-sm text-gray-500">{{ __('No payments recorded for this date.') }}</p>
                        @else
                            <p class="text-sm text-gray-800">{{ $daily['services']->implode(', ') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-0">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-black text-amber-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('Service Revenue Report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('all time') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-black">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                        <th class="px-4 py-3">{{ __('Service') }}</th>
                                        <th class="px-4 py-3 text-right">{{ __('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($revenueByService as $service => $amount)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $service }}</td>
                                            <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">₦{{ number_format($amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No revenue recorded yet.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-amber-50">
                                        <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ __('TOTAL') }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">₦{{ number_format($revenueByService->sum(), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-0">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-black text-amber-400">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ __('Outstanding Report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Students who currently owe a balance') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-black">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                        <th class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                {{ __('Student') }}
                                            </span>
                                        </th>
                                        <th class="px-4 py-3">{{ __('Service') }}</th>
                                        <th class="px-4 py-3">{{ __('Total') }}</th>
                                        <th class="px-4 py-3">{{ __('Paid') }}</th>
                                        <th class="px-4 py-3">{{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($outstanding as $row)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                    </span>
                                                    <a href="{{ route('students.show', $row['student_id']) }}" class="font-semibold text-amber-600 hover:underline">{{ $row['student'] }}</a>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['label'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">₦{{ number_format($row['price'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">₦{{ number_format($row['paid'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-red-600">₦{{ number_format($row['balance'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No students currently owe anything.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if ($outstanding->isNotEmpty())
                                    <tfoot>
                                        <tr class="bg-red-50">
                                            <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-900 text-right">{{ __('Total Outstanding') }}</td>
                                            <td class="px-4 py-3 text-sm font-bold text-red-600">₦{{ number_format($outstanding->sum('balance'), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden border-t border-gray-100 bg-gray-50/60 px-6 sm:px-8 py-5">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M3 9l12 12M3 15l6 6" /></svg>
                    <div class="relative flex items-center gap-3">
                        <x-application-logo class="h-8 w-8 shrink-0 object-contain" />
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Training Safe Drivers, Building Better Roads.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
