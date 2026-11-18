<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Statements') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Income Statement & Balance Sheet') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Built directly from the General Ledger — revenue, expenses, and the school\'s financial position for the year') }}</p>
                </div>
                <form method="get" action="{{ route('financial-statements.index') }}">
                    <select name="year" class="rounded-lg ring-1 ring-gray-300 border-0 focus:border-gold-500 focus:ring-gold-500 text-sm font-semibold" onchange="this.form.submit()">
                        @foreach (range(now()->year + 1, now()->year - 5) as $selectableYear)
                            <option value="{{ $selectableYear }}" @selected($selectableYear === $year)>{{ $selectableYear }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('financial-statements.export', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3h6m-6 3h6M6 21h12a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18 4.5h-7.5L6 9v9.75A2.25 2.25 0 0 0 8.25 21H6Z" /></svg>
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('financial-statements.export-pdf', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('general-ledger.index', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('General Ledger') }}
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Income Statement — :year', ['year' => $year]) }}</h3>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('Revenue') }}</p>
                    <table class="min-w-full text-sm mb-4">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($incomeStatement['revenue'] as $account => $amount)
                                <tr>
                                    <td class="py-1.5 text-gray-700">{{ $account }}</td>
                                    <td class="py-1.5 text-right text-gray-900">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-1.5 text-gray-500" colspan="2">{{ __('No revenue recorded.') }}</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold border-t border-gray-300">
                                <td class="py-1.5 text-gray-900">{{ __('Total Revenue') }}</td>
                                <td class="py-1.5 text-right text-gray-900">₦{{ number_format($incomeStatement['total_revenue'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('Expenses') }}</p>
                    <table class="min-w-full text-sm mb-4">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($incomeStatement['expenses'] as $account => $amount)
                                <tr>
                                    <td class="py-1.5 text-gray-700">{{ $account }}</td>
                                    <td class="py-1.5 text-right text-gray-900">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="py-1.5 text-gray-500" colspan="2">{{ __('No expenses recorded.') }}</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold border-t border-gray-300">
                                <td class="py-1.5 text-gray-900">{{ __('Total Expenses') }}</td>
                                <td class="py-1.5 text-right text-gray-900">₦{{ number_format($incomeStatement['total_expenses'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="flex items-center justify-between rounded-lg {{ $incomeStatement['net_income'] >= 0 ? 'bg-green-50' : 'bg-red-50' }} px-4 py-3">
                        <span class="font-bold text-gray-900">{{ __('Net Income') }}</span>
                        <span class="font-bold {{ $incomeStatement['net_income'] >= 0 ? 'text-green-700' : 'text-red-700' }}">₦{{ number_format($incomeStatement['net_income'], 2) }}</span>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('Balance Sheet — as of :date', ['date' => $asOfDate]) }}</h3>
                        <x-badge :color="$balanceSheet['balanced'] ? 'green' : 'red'">
                            {{ $balanceSheet['balanced'] ? __('Balanced') : __('Out of Balance') }}
                        </x-badge>
                    </div>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('Assets') }}</p>
                    <table class="min-w-full text-sm mb-4">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($balanceSheet['assets'] as $account => $amount)
                                <tr>
                                    <td class="py-1.5 text-gray-700">{{ $account }}</td>
                                    <td class="py-1.5 text-right text-gray-900">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold border-t border-gray-300">
                                <td class="py-1.5 text-gray-900">{{ __('Total Assets') }}</td>
                                <td class="py-1.5 text-right text-gray-900">₦{{ number_format($balanceSheet['total_assets'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('Liabilities') }}</p>
                    <table class="min-w-full text-sm mb-4">
                        <tbody>
                            <tr><td class="py-1.5 text-gray-500" colspan="2">{{ __('None tracked.') }}</td></tr>
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold border-t border-gray-300">
                                <td class="py-1.5 text-gray-900">{{ __('Total Liabilities') }}</td>
                                <td class="py-1.5 text-right text-gray-900">₦{{ number_format($balanceSheet['total_liabilities'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ __('Equity') }}</p>
                    <table class="min-w-full text-sm mb-4">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($balanceSheet['equity'] as $account => $amount)
                                <tr>
                                    <td class="py-1.5 text-gray-700">{{ $account }}</td>
                                    <td class="py-1.5 text-right text-gray-900">₦{{ number_format($amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold border-t border-gray-300">
                                <td class="py-1.5 text-gray-900">{{ __('Total Equity') }}</td>
                                <td class="py-1.5 text-right text-gray-900">₦{{ number_format($balanceSheet['total_equity'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
