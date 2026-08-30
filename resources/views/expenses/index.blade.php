<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expenses') }}
        </h2>
    </x-slot>

    @php
        $walletIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $trendIconPath = 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $funnelIconPath = 'M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z';
        $boltIconPath = 'M3.75 13.5 14.25 2.25 12 10.5h8.25L9.75 21.75 12 13.5H3.75Z';
        $giftIconPath = 'M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21M3.375 7.5h17.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125H3.375A1.125 1.125 0 0 1 2.25 10.875v-2.25C2.25 8.004 2.754 7.5 3.375 7.5Z';
        $lightBulbIconPath = 'M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18';
        $homeIconPath = 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25';
        $vehicleIconPath = 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12';
        $banknoteIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $shoppingBagIconPath = 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.119-1.243l1.263-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.06.435 1.119 1.007Z';

        $categoryIcon = [
            'fuel' => $boltIconPath,
            'gift' => $giftIconPath,
            'house_materials' => $homeIconPath,
            'office_accessory' => $lightBulbIconPath,
            'electricity' => $lightBulbIconPath,
            'office_rent' => $lightBulbIconPath,
            'office_tools_repair' => $lightBulbIconPath,
            'internet' => $lightBulbIconPath,
            'marketing' => $lightBulbIconPath,
            'salary' => $banknoteIconPath,
            'debt' => $banknoteIconPath,
            'dssp_payment' => $banknoteIconPath,
            'investment_saving' => $banknoteIconPath,
            'car_repair' => $vehicleIconPath,
            'car_bodywork' => $vehicleIconPath,
            'new_car' => $vehicleIconPath,
            'new_engine' => $vehicleIconPath,
            'vehicle_insurance' => $vehicleIconPath,
            'vehicle_registration' => $vehicleIconPath,
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Expenses') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Track and manage all your expenses') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $walletIconPath }}" /></svg>
                </span>
            </div>

            @if (session('status') === 'expense-created')
                <p class="text-sm font-medium text-green-600">{{ __('Expense recorded successfully.') }}</p>
            @elseif (session('status') === 'expense-updated')
                <p class="text-sm font-medium text-green-600">{{ __('Expense updated successfully.') }}</p>
            @elseif (session('status') === 'expense-deleted')
                <p class="text-sm font-medium text-green-600">{{ __('Expense removed successfully.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trendIconPath }}" /></svg>
                        </span>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ __('Finance Summary') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('This Month') }}</p>
                            <p class="text-3xl font-extrabold text-gray-900 mt-1">₦{{ number_format($totalThisMonth, 2) }}</p>
                            @if (! is_null($percentChange))
                                <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $percentChange <= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        @if ($percentChange <= 0)
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                                        @endif
                                    </svg>
                                    {{ abs($percentChange) }}% {{ __('vs last month') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ __('Record Expense') }}
                    </a>
                </div>
            </div>

            <form method="get" action="{{ route('expenses.index') }}" class="flex flex-wrap gap-3">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                    <select name="period" onchange="this.form.submit()" class="pl-9 pr-8 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium">
                        <option value="all_time" @selected($period === 'all_time')>{{ __('All Time') }}</option>
                        <option value="this_month" @selected($period === 'this_month')>{{ __('This Month') }}</option>
                        <option value="last_month" @selected($period === 'last_month')>{{ __('Last Month') }}</option>
                        <option value="this_year" @selected($period === 'this_year')>{{ __('This Year') }}</option>
                    </select>
                </div>

                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $funnelIconPath }}" /></svg>
                    <select name="category" onchange="this.form.submit()" class="pl-9 pr-8 rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach (\App\Models\Expense::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" @selected($category === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                        {{ __('Date') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">{{ __('Category') }}</th>
                                <th class="px-4 py-3">{{ __('Amount') }}</th>
                                <th class="px-4 py-3">{{ __('Description') }}</th>
                                <th class="px-4 py-3">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <p class="font-semibold text-gray-800">{{ $expense->expense_date->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $expense->expense_date->format('D') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $categoryIcon[$expense->category] ?? $shoppingBagIconPath }}" /></svg>
                                            </span>
                                            <span class="text-gray-700">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top font-semibold text-gray-800">₦{{ number_format($expense->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ \Illuminate\Support\Str::limit($expense->description, 40) ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('expenses.show', $expense) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                <a href="{{ route('expenses.edit', $expense) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                <form method="post" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this expense?') }}');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No expenses recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expenses->links() }}
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-6 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknoteIconPath }}" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total Expenses') }}</p>
                            <p class="text-lg font-extrabold text-gray-900">₦{{ number_format($totalExpenses, 2) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total Transactions') }}</p>
                            <p class="text-lg font-extrabold text-gray-900">{{ number_format($totalTransactions) }}</p>
                        </div>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
