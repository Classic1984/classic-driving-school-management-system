<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance Summary') }}
        </h2>
    </x-slot>

    @php
        $totalOriginalFees = $discounts->sum(fn ($enrollment) => $enrollment->originalFee());
        $totalDiscountPercent = $totalOriginalFees > 0 ? round($discounts->sum('discount_amount') / $totalOriginalFees * 100, 2) : 0;
        $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Auto & Manual'];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Expenses & Balance') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Track income, expenses, and discounts by year') }}</p>
                </div>
                <form method="get" action="{{ route('finance.summary') }}">
                    <select name="year" class="rounded-lg ring-1 ring-gray-300 border-0 focus:border-amber-500 focus:ring-amber-500 text-sm font-semibold" onchange="this.form.submit()">
                        @foreach (range(now()->year, now()->year - 5) as $selectableYear)
                            <option value="{{ $selectableYear }}" @selected($selectableYear === $year)>{{ $selectableYear }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="flex flex-wrap gap-3 mb-6">
                <a href="{{ route('finance.export', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3h6m-6 3h6M6 21h12a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18 4.5h-7.5L6 9v9.75A2.25 2.25 0 0 0 8.25 21H6Z" /></svg>
                    {{ __('Export CSV') }}
                </a>
                <a href="{{ route('finance.export-pdf', ['year' => $year]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('payment-reports.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-blue-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    {{ __('Financial Reports') }}
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ __('Total Balance') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-black text-amber-400 rounded-lg p-4">
                                <p class="text-xs uppercase tracking-wider">{{ __('Total Income') }}</p>
                                <p class="text-2xl font-bold mt-1">₦{{ number_format($overall['income'], 2) }}</p>
                            </div>
                            <div class="bg-black text-amber-400 rounded-lg p-4">
                                <p class="text-xs uppercase tracking-wider">{{ __('Total Expenses') }}</p>
                                <p class="text-2xl font-bold mt-1">₦{{ number_format($overall['expenses'], 2) }}</p>
                            </div>
                            <div class="bg-amber-500 text-black rounded-lg p-4">
                                <p class="text-xs uppercase tracking-wider">{{ __('Balance') }}</p>
                                <p class="text-2xl font-bold mt-1">₦{{ number_format($overall['balance'], 2) }}</p>
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
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
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

                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-3 p-6 pb-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v12a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18v-1.5m-18-9h18m-18 0A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25m-18 0v9m18-9v9" /></svg>
                            </span>
                            <div>
                                <p class="text-xl font-extrabold text-amber-600 leading-none">{{ $year }}</p>
                                <p class="mt-0.5 text-sm text-gray-500">{{ __('Monthly Overview') }}</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto px-6 pb-6">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        <th class="px-2 py-2">{{ __('Month') }}</th>
                                        <th class="px-2 py-2">{{ __('Income (₦)') }}</th>
                                        <th class="px-2 py-2">{{ __('Expenses (₦)') }}</th>
                                        <th class="px-2 py-2">{{ __('Balance (₦)') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($months as $month)
                                        @php $hasActivity = $month['income'] > 0 || $month['expenses'] > 0; @endphp
                                        <tr class="{{ $hasActivity ? 'bg-amber-50/60' : '' }}">
                                            <td class="px-2 py-2 text-sm">
                                                <span class="inline-flex items-center gap-2">
                                                    <svg class="h-4 w-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                                    {{ $month['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2 text-sm">{{ number_format($month['income'], 2) }}</td>
                                            <td class="px-2 py-2 text-sm">{{ number_format($month['expenses'], 2) }}</td>
                                            <td class="px-2 py-2 text-sm font-medium {{ $month['balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($month['balance'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-amber-100/70 border-t-2 border-amber-300">
                                        <td class="px-2 py-2 text-sm font-bold">{{ __('Year Total') }}</td>
                                        <td class="px-2 py-2 text-sm font-bold">{{ number_format($totals['income'], 2) }}</td>
                                        <td class="px-2 py-2 text-sm font-bold">{{ number_format($totals['expenses'], 2) }}</td>
                                        <td class="px-2 py-2 text-sm font-bold {{ $totals['balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($totals['balance'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    @if ($discounts->isNotEmpty())
                        <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 p-6 pb-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.83.699 2.528 0l4.318-4.318a1.79 1.79 0 0 0 0-2.528L10.505 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                </span>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('Discounts') }} — {{ $year }}</h3>
                            </div>

                            <div class="overflow-x-auto px-6 pb-6">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead>
                                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            <th class="px-2 py-2">{{ __('Student') }}</th>
                                            <th class="px-2 py-2">{{ __('Course') }}</th>
                                            <th class="px-2 py-2">{{ __('Original Fee (₦)') }}</th>
                                            <th class="px-2 py-2">{{ __('Discount (₦)') }}</th>
                                            <th class="px-2 py-2">{{ __('Discount %') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @foreach ($discounts as $enrollment)
                                            @php
                                                $initials = collect(explode(' ', $enrollment->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                                $discountPercent = $enrollment->originalFee() > 0 ? round($enrollment->discount_amount / $enrollment->originalFee() * 100, 2) : 0;
                                                $experienceLabel = is_null($enrollment->student->has_driving_experience) ? null : ($enrollment->student->has_driving_experience ? __('Partial Experience') : __('Non-Experience'));
                                                $transmissionLabel = $transmissionLabels[$enrollment->course->course_type] ?? null;
                                            @endphp
                                            <tr>
                                                <td class="px-2 py-2 text-sm">
                                                    <div class="flex items-center gap-2">
                                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-xs font-bold text-amber-400">{{ $initials }}</span>
                                                        <div>
                                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="font-medium text-amber-600 hover:underline">{{ $enrollment->student->name }}</a>
                                                            <p class="text-xs text-gray-400">
                                                                {{ __('Approved by :name', ['name' => $enrollment->discountApprovedBy?->name ?? '—']) }}
                                                                &middot; {{ config("discounts.reasons.{$enrollment->discount_reason}", $enrollment->discount_reason) }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-2 py-2 text-sm">
                                                    <p class="text-gray-800">{{ $enrollment->course->name }}</p>
                                                    <p class="text-xs text-gray-400">
                                                        @if ($experienceLabel && $transmissionLabel)
                                                            {{ __(':experience (:transmission)', ['experience' => $experienceLabel, 'transmission' => $transmissionLabel]) }}
                                                        @elseif ($transmissionLabel)
                                                            {{ $transmissionLabel }}
                                                        @endif
                                                        &middot; {{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}
                                                    </p>
                                                </td>
                                                <td class="px-2 py-2 text-sm">{{ number_format($enrollment->originalFee(), 2) }}</td>
                                                <td class="px-2 py-2 text-sm font-medium text-green-600">{{ number_format($enrollment->discount_amount, 2) }}</td>
                                                <td class="px-2 py-2 text-sm">
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">{{ $discountPercent }}%</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-amber-100/70 border-t-2 border-amber-300">
                                            <td colspan="2" class="px-2 py-2 text-sm font-bold">{{ __('Total Discounts') }}</td>
                                            <td></td>
                                            <td class="px-2 py-2 text-sm font-bold text-green-600">{{ number_format($discounts->sum('discount_amount'), 2) }}</td>
                                            <td class="px-2 py-2 text-sm">
                                                <span class="inline-flex items-center rounded-full bg-amber-200 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $totalDiscountPercent }}%</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                        <a href="{{ route('payment-reports.index') }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <svg class="h-5 w-5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3h7.5M8.25 9h.008v.008H8.25V9Z" /></svg>
                            <span class="text-sm font-semibold text-gray-800">{{ __('Financial Reports') }}</span>
                        </a>
                        <a href="{{ route('finance.export-pdf', ['year' => $year]) }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3.75H6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21.75h12a2.25 2.25 0 0 0 2.25-2.25V11.25a9 9 0 0 0-9-9v1.5" /></svg>
                            <span class="text-sm font-semibold text-gray-800">{{ __('Download PDF') }}</span>
                        </a>
                        <a href="{{ route('finance.export', ['year' => $year]) }}" class="flex items-center gap-3 px-5 py-4 hover:bg-gray-50 border-b border-gray-100 transition">
                            <svg class="h-5 w-5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3h6m-6 3h6M6 21h12a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 18 4.5h-7.5L6 9v9.75A2.25 2.25 0 0 0 8.25 21H6Z" /></svg>
                            <span class="text-sm font-semibold text-gray-800">{{ __('Export CSV') }}</span>
                        </a>
                        <a href="{{ route('expenses.index') }}" class="flex items-center justify-between gap-3 px-5 py-4 bg-amber-50 hover:bg-amber-100 transition">
                            <span class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0v6a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-6m-19.5 0h19.5M4.5 9.75V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 0 1 11.048 0c1.131.094 1.976 1.057 1.976 2.192V9.75" /></svg>
                                <span class="text-sm font-semibold text-amber-800">{{ __('Manage Expenses') }}</span>
                            </span>
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </a>
                    </div>

                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 text-center">
                        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-amber-50">
                            <svg class="h-10 w-10 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">{{ __('Manage Your Expenses') }}</h4>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Add, edit and track all expenses to keep your records accurate and up to date.') }}</p>
                        <a href="{{ route('expenses.index') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                            {{ __('Manage Expenses') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
