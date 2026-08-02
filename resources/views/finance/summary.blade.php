<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance Summary') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Income, Expenses & Balance') }} — {{ $year }}
                    </h3>

                    <div class="flex items-center gap-4">
                        <form method="get" action="{{ route('finance.summary') }}" class="flex items-center gap-2">
                            <select name="year" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                @foreach (range(now()->year, now()->year - 5) as $selectableYear)
                                    <option value="{{ $selectableYear }}" @selected($selectableYear === $year)>{{ $selectableYear }}</option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('expenses.index') }}">
                            <x-primary-button type="button">{{ __('Manage Expenses') }}</x-primary-button>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Month') }}</th>
                                <th class="px-4 py-2">{{ __('Income') }}</th>
                                <th class="px-4 py-2">{{ __('Expenses') }}</th>
                                <th class="px-4 py-2">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($months as $month)
                                <tr>
                                    <td class="px-4 py-2 text-sm">{{ $month['label'] }}</td>
                                    <td class="px-4 py-2 text-sm">{{ number_format($month['income'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ number_format($month['expenses'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium {{ $month['balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($month['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-300">
                                <td class="px-4 py-2 text-sm font-semibold">{{ __('Year Total') }}</td>
                                <td class="px-4 py-2 text-sm font-semibold">{{ number_format($totals['income'], 2) }}</td>
                                <td class="px-4 py-2 text-sm font-semibold">{{ number_format($totals['expenses'], 2) }}</td>
                                <td class="px-4 py-2 text-sm font-semibold {{ $totals['balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($totals['balance'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
