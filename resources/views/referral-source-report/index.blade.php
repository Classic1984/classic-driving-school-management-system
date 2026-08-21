<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referral Source Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 print:hidden">
                    <div class="flex items-center gap-2">
                        @foreach (['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'] as $value => $tabLabel)
                            <a
                                href="{{ route('referral-source-report.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm rounded-md {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()">
                            <x-secondary-button type="button">{{ __('Print') }}</x-secondary-button>
                        </button>
                        <a href="{{ route('referral-source-report.export', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('referral-source-report.export-pdf', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-black text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Total Students') }}</p>
                        <p class="text-2xl font-bold mt-1">{{ $summary['total'] }}</p>
                    </div>
                    <div class="bg-gray-900 text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Revenue Collected') }}</p>
                        <p class="text-2xl font-bold mt-1">₦{{ number_format($summary['revenue'], 2) }}</p>
                    </div>
                    <div class="bg-amber-500 text-black rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Outstanding Balance') }}</p>
                        <p class="text-2xl font-bold mt-1">₦{{ number_format($summary['outstanding'], 2) }}</p>
                    </div>
                </div>

                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('By Source') }}</h3>
                <p class="text-xs text-gray-500 mb-2">{{ __('Ordered by revenue collected — the channel actually worth the most, not just the one with the most sign-ups.') }}</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Source') }}</th>
                                <th class="px-4 py-2">{{ __('Total') }}</th>
                                <th class="px-4 py-2">{{ __('Active') }}</th>
                                <th class="px-4 py-2">{{ __('Completed') }}</th>
                                <th class="px-4 py-2">{{ __('Withdrawn') }}</th>
                                <th class="px-4 py-2">{{ __('Completion Rate') }}</th>
                                <th class="px-4 py-2">{{ __('Revenue Collected') }}</th>
                                <th class="px-4 py-2">{{ __('Outstanding') }}</th>
                                <th class="px-4 py-2">{{ __('Avg. Revenue / Student') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-medium">{{ __($row['source']) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['total'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['active'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['completed'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['withdrawn'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['completion_rate'] }}%</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($row['revenue'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($row['outstanding'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($row['avg_revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No students registered during this period.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
