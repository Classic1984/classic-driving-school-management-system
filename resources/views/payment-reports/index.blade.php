<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Reports') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('payment-reports.export') }}">
                    <x-secondary-button type="button">{{ __('Export Outstanding CSV') }}</x-secondary-button>
                </a>
                <a href="{{ route('payment-reports.export-pdf', ['date' => $date]) }}">
                    <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                </a>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">{{ __('Daily Payment Report') }}</h3>
                    <form method="get" action="{{ route('payment-reports.index') }}">
                        <input type="date" name="date" value="{{ $date }}" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()" max="{{ now()->format('Y-m-d') }}">
                    </form>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="bg-black text-amber-400 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider">{{ __('Payments') }}</p>
                        <p class="text-2xl font-bold mt-1">{{ $daily['count'] }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Cash') }}</p>
                        <p class="text-xl font-bold mt-1">₦{{ number_format($daily['cash'], 2) }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Transfers') }}</p>
                        <p class="text-xl font-bold mt-1">₦{{ number_format($daily['bank_transfer'], 2) }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('POS') }}</p>
                        <p class="text-xl font-bold mt-1">₦{{ number_format($daily['card'], 2) }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('Online') }}</p>
                        <p class="text-xl font-bold mt-1">₦{{ number_format($daily['mobile_money'], 2) }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Services Paid For') }}</p>
                    @if ($daily['services']->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('No payments recorded for this date.') }}</p>
                    @else
                        <p class="text-sm text-gray-800">{{ $daily['services']->implode(', ') }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Service Revenue Report') }} <span class="text-sm font-normal text-gray-500">({{ __('all time') }})</span></h3>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-2">{{ __('Service') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($revenueByService as $service => $amount)
                            <tr>
                                <td class="px-4 py-2 text-sm">{{ $service }}</td>
                                <td class="px-4 py-2 text-sm text-right">₦{{ number_format($amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-2 text-sm text-gray-500">{{ __('No revenue recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300 font-semibold">
                            <td class="px-4 py-2 text-sm">{{ __('TOTAL') }}</td>
                            <td class="px-4 py-2 text-sm text-right">₦{{ number_format($revenueByService->sum(), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Outstanding Report') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Service') }}</th>
                                <th class="px-4 py-2">{{ __('Total') }}</th>
                                <th class="px-4 py-2">{{ __('Paid') }}</th>
                                <th class="px-4 py-2">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($outstanding as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.show', $row['student_id']) }}" class="text-amber-600 hover:underline">{{ $row['student'] }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $row['label'] }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($row['price'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($row['paid'], 2) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-red-600">₦{{ number_format($row['balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-sm text-gray-500">{{ __('No students currently owe anything.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($outstanding->isNotEmpty())
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 font-semibold">
                                    <td colspan="4" class="px-4 py-2 text-sm text-right">{{ __('Total Outstanding') }}</td>
                                    <td class="px-4 py-2 text-sm text-red-600">₦{{ number_format($outstanding->sum('balance'), 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
