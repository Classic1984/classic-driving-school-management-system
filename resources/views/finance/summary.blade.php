<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance Summary') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 mb-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ __("Today's Balance") }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-black text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Income') }}</p>
                        <p class="text-2xl font-bold mt-1">₦{{ number_format($today['income'], 2) }}</p>
                    </div>
                    <div class="bg-black text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Expenses') }}</p>
                        <p class="text-2xl font-bold mt-1">₦{{ number_format($today['expenses'], 2) }}</p>
                    </div>
                    <div class="bg-amber-500 text-black rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Balance') }}</p>
                        <p class="text-2xl font-bold mt-1">₦{{ number_format($today['balance'], 2) }}</p>
                    </div>
                </div>
            </div>

            @php
                $chartMax = max(1, $months->max('income'), $months->max('expenses'));
                $chartHeight = 200;
                $groupWidth = 70;
                $barWidth = 22;
                $chartWidth = $groupWidth * 12;
            @endphp
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Revenue Trend') }} — {{ $year }}</h3>
                    <div class="flex items-center gap-4 text-xs text-gray-600">
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 bg-amber-500 rounded-sm"></span> {{ __('Income') }}</span>
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 bg-gray-900 rounded-sm"></span> {{ __('Expenses') }}</span>
                    </div>
                </div>

                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 30 }}" class="w-full" style="max-height: 260px;">
                    @foreach ($months as $index => $month)
                        @php
                            $groupX = $index * $groupWidth;
                            $incomeHeight = round(($month['income'] / $chartMax) * $chartHeight);
                            $expensesHeight = round(($month['expenses'] / $chartMax) * $chartHeight);
                        @endphp
                        <rect
                            x="{{ $groupX + ($groupWidth - $barWidth) / 2 - $barWidth / 2 - 2 }}"
                            y="{{ $chartHeight - $incomeHeight }}"
                            width="{{ $barWidth / 2 }}"
                            height="{{ $incomeHeight }}"
                            fill="#f59e0b"
                        >
                            <title>{{ $month['label'] }} Income: ₦{{ number_format($month['income'], 2) }}</title>
                        </rect>
                        <rect
                            x="{{ $groupX + ($groupWidth - $barWidth) / 2 + $barWidth / 2 + 2 }}"
                            y="{{ $chartHeight - $expensesHeight }}"
                            width="{{ $barWidth / 2 }}"
                            height="{{ $expensesHeight }}"
                            fill="#111827"
                        >
                            <title>{{ $month['label'] }} Expenses: ₦{{ number_format($month['expenses'], 2) }}</title>
                        </rect>
                        <text
                            x="{{ $groupX + $groupWidth / 2 }}"
                            y="{{ $chartHeight + 18 }}"
                            text-anchor="middle"
                            font-size="10"
                            fill="#6b7280"
                        >{{ substr($month['label'], 0, 3) }}</text>
                    @endforeach
                    <line x1="0" y1="{{ $chartHeight }}" x2="{{ $chartWidth }}" y2="{{ $chartHeight }}" stroke="#d1d5db" stroke-width="1" />
                </svg>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
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
                        <a href="{{ route('finance.export', ['year' => $year]) }}">
                            <x-secondary-button type="button">{{ __('Export CSV') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('finance.export-pdf', ['year' => $year]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
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

            @if ($discounts->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Discounts') }} — {{ $year }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-4 py-2">{{ __('Student') }}</th>
                                    <th class="px-4 py-2">{{ __('Course') }}</th>
                                    <th class="px-4 py-2">{{ __('Original Fee') }}</th>
                                    <th class="px-4 py-2">{{ __('Discount') }}</th>
                                    <th class="px-4 py-2">{{ __('Final Fee') }}</th>
                                    <th class="px-4 py-2">{{ __('Reason') }}</th>
                                    <th class="px-4 py-2">{{ __('Approved By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($discounts as $enrollment)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                                {{ $enrollment->student->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ $enrollment->course->name }}</td>
                                        <td class="px-4 py-2 text-sm">{{ number_format($enrollment->originalFee(), 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-green-600">{{ number_format($enrollment->discount_amount, 2) }}</td>
                                        <td class="px-4 py-2 text-sm font-medium">{{ number_format($enrollment->fee(), 2) }}</td>
                                        <td class="px-4 py-2 text-sm">{{ config("discounts.reasons.{$enrollment->discount_reason}", $enrollment->discount_reason) }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $enrollment->discountApprovedBy?->name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300">
                                    <td colspan="3" class="px-4 py-2 text-sm font-semibold">{{ __('Total Discounted') }}</td>
                                    <td class="px-4 py-2 text-sm font-semibold text-green-600">{{ number_format($discounts->sum('discount_amount'), 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
