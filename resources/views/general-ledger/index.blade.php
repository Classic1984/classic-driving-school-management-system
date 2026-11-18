<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('General Ledger') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Trial Balance & Ledger Entries') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Every paid payment and paid expense, as a balanced double-entry record') }}</p>
                </div>
                <form method="get" action="{{ route('general-ledger.index') }}">
                    <select name="year" class="rounded-lg ring-1 ring-gray-300 border-0 focus:border-gold-500 focus:ring-gold-500 text-sm font-semibold" onchange="this.form.submit()">
                        @foreach (range(now()->year, now()->year - 5) as $selectableYear)
                            <option value="{{ $selectableYear }}" @selected($selectableYear === $year)>{{ $selectableYear }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('general-ledger.export', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3h6m-6 3h6M6 21h12a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18 4.5h-7.5L6 9v9.75A2.25 2.25 0 0 0 8.25 21H6Z" /></svg>
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('general-ledger.export-pdf', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('finance.summary', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Finance Summary') }}
                </a>
                <a href="{{ route('payment-reports.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50 transition">
                    {{ __('Financial Reports') }}
                </a>
                <a href="{{ route('financial-statements.index', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Financial Statements') }}
                </a>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('Trial Balance') }}</h3>
                    <x-badge :color="$trialBalance['balanced'] ? 'green' : 'red'">
                        {{ $trialBalance['balanced'] ? __('Balanced') : __('Out of Balance') }}
                    </x-badge>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Account') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Debit') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Credit') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($trialBalance['accounts'] as $account => $amounts)
                                <tr>
                                    <td class="px-4 py-2 text-gray-900">{{ $account }}</td>
                                    <td class="px-4 py-2 text-right text-gray-700">{{ $amounts['debit'] > 0 ? '₦'.number_format($amounts['debit'], 2) : '—' }}</td>
                                    <td class="px-4 py-2 text-right text-gray-700">{{ $amounts['credit'] > 0 ? '₦'.number_format($amounts['credit'], 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ __('No paid payments or expenses recorded for :year.', ['year' => $year]) }}</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t-2 border-gray-300">
                                <td class="px-4 py-2 text-gray-900">{{ __('Total') }}</td>
                                <td class="px-4 py-2 text-right text-gray-900">₦{{ number_format($trialBalance['total_debit'], 2) }}</td>
                                <td class="px-4 py-2 text-right text-gray-900">₦{{ number_format($trialBalance['total_credit'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Ledger Entries') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Reference') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Debit') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Credit') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($entries as $entry)
                                <tr>
                                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ $entry['date']->format('M j, Y') }}</td>
                                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ $entry['reference'] }}</td>
                                    <td class="px-4 py-2 text-gray-900">{{ $entry['description'] }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $entry['debit_account'] }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $entry['credit_account'] }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900 font-semibold whitespace-nowrap">₦{{ number_format($entry['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">{{ __('No ledger entries for :year.', ['year' => $year]) }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
