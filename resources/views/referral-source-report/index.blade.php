<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referral Source Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    @php
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $exclamationIconPath = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
        $tagIconPath = 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
                        <div class="inline-flex items-center gap-1 rounded-full bg-gray-100 p-1">
                            @foreach (['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'] as $value => $tabLabel)
                                <a
                                    href="{{ route('referral-source-report.index', ['period' => $value]) }}"
                                    class="px-4 py-1.5 text-sm font-semibold rounded-full transition {{ $period === $value ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900' }}"
                                >{{ __($tabLabel) }}</a>
                            @endforeach
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                                {{ __('Print') }}
                            </button>
                            <a href="{{ route('referral-source-report.export', ['period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                {{ __('Export Excel') }}
                            </a>
                            <a href="{{ route('referral-source-report.export-pdf', ['period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                                {{ __('Download PDF') }}
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6">
                        <div class="relative overflow-hidden rounded-xl bg-black p-4">
                            <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                            <p class="relative text-xs uppercase tracking-wider text-amber-400/80">{{ __('Total Students') }}</p>
                            <p class="relative text-2xl font-extrabold text-amber-400 mt-1">{{ $summary['total'] }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-gray-900 p-4">
                            <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                            <p class="relative text-xs uppercase tracking-wider text-amber-400/80">{{ __('Revenue Collected') }}</p>
                            <p class="relative text-2xl font-extrabold text-amber-400 mt-1">₦{{ number_format($summary['revenue'], 2) }}</p>
                        </div>
                        <div class="relative overflow-hidden rounded-xl bg-amber-400 p-4">
                            <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-black opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $exclamationIconPath }}" /></svg>
                            <p class="relative text-xs uppercase tracking-wider text-black/70">{{ __('Outstanding Balance') }}</p>
                            <p class="relative text-2xl font-extrabold text-black mt-1">₦{{ number_format($summary['outstanding'], 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-1">{{ __('By Source') }}</h3>
                    <p class="text-xs text-gray-500 mb-3">{{ __('Ordered by revenue collected — the channel actually worth the most, not just the one with the most sign-ups.') }}</p>

                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-black">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                        <th class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tagIconPath }}" /></svg>
                                                {{ __('Source') }}
                                            </span>
                                        </th>
                                        <th class="px-4 py-3">{{ __('Total') }}</th>
                                        <th class="px-4 py-3">{{ __('Active') }}</th>
                                        <th class="px-4 py-3">{{ __('Completed') }}</th>
                                        <th class="px-4 py-3">{{ __('Withdrawn') }}</th>
                                        <th class="px-4 py-3">{{ __('Completion Rate') }}</th>
                                        <th class="px-4 py-3">{{ __('Revenue Collected') }}</th>
                                        <th class="px-4 py-3">{{ __('Outstanding') }}</th>
                                        <th class="px-4 py-3">{{ __('Avg. Revenue / Student') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ __($row['source']) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['total'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['active'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['completed'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['withdrawn'] }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['completion_rate'] }}%</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">₦{{ number_format($row['revenue'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-red-600">₦{{ number_format($row['outstanding'], 2) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">₦{{ number_format($row['avg_revenue'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-4 py-10 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                                    </span>
                                                    <p class="text-sm text-gray-500">{{ __('No students registered during this period.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
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
