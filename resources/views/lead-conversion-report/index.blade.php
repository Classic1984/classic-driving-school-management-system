<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lead Conversion Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 print:hidden">
                    <div class="flex items-center gap-2">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $value => $tabLabel)
                            <a
                                href="{{ route('lead-conversion-report.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm rounded-md {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()">
                            <x-secondary-button type="button">{{ __('Print') }}</x-secondary-button>
                        </button>
                        <a href="{{ route('lead-conversion-report.export', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('lead-conversion-report.export-pdf', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-black text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Total Leads') }}</p>
                        <p class="text-2xl font-bold mt-1">{{ $summary['total'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('New') }}</p>
                        <p class="text-xl font-bold mt-1">{{ $summary['new'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Contacted') }}</p>
                        <p class="text-xl font-bold mt-1">{{ $summary['contacted'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Converted') }}</p>
                        <p class="text-xl font-bold mt-1 text-green-600">{{ $summary['converted'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Lost') }}</p>
                        <p class="text-xl font-bold mt-1 text-red-600">{{ $summary['lost'] }}</p>
                    </div>
                    <div class="bg-amber-500 text-black rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Conversion Rate') }}</p>
                        <p class="text-2xl font-bold mt-1">{{ $summary['rate'] }}%</p>
                    </div>
                </div>

                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('By Source') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Source') }}</th>
                                <th class="px-4 py-2">{{ __('Total') }}</th>
                                <th class="px-4 py-2">{{ __('New') }}</th>
                                <th class="px-4 py-2">{{ __('Contacted') }}</th>
                                <th class="px-4 py-2">{{ __('Converted') }}</th>
                                <th class="px-4 py-2">{{ __('Lost') }}</th>
                                <th class="px-4 py-2">{{ __('Conversion Rate') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ __($row['source']) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['total'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['new'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['contacted'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['converted'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['lost'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $row['rate'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No inquiries were logged during this period.') }}
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
