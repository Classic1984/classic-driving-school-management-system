<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payroll Run — :month', ['month' => $payrollRun->month->format('F Y')]) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'payroll-run-paid')
                <div class="rounded-lg bg-green-50 ring-1 ring-green-200 text-green-800 px-4 py-3 text-sm">{{ __('Payroll processed — an Expense was recorded for each person.') }}</div>
            @endif

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ $payrollRun->month->format('F Y') }}</h3>
                    <p class="text-sm text-gray-500">
                        <x-badge :color="$payrollRun->isPaid() ? 'green' : 'amber'" class="capitalize">{{ $payrollRun->status }}</x-badge>
                        @if ($payrollRun->isPaid())
                            {{ __('Processed by :name on :date', ['name' => $payrollRun->processedBy?->name ?? 'Unknown', 'date' => $payrollRun->processed_at->format('M j, Y g:i A')]) }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('payroll.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Back to Payroll') }}
                </a>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Payslips') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Name') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Amount') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($payrollRun->items as $item)
                                <tr>
                                    <td class="px-4 py-2 text-gray-900 font-medium">{{ $item->payee_name }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900">₦{{ number_format($item->amount, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        @if ($payrollRun->isPaid())
                                            <a href="{{ route('payroll.runs.payslip', [$payrollRun, $item]) }}" class="text-sm font-semibold text-gold-700 hover:text-gold-800">{{ __('Payslip') }}</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t-2 border-gray-300">
                                <td class="px-4 py-2 text-gray-900">{{ __('Total') }}</td>
                                <td class="px-4 py-2 text-right text-gray-900">₦{{ number_format($payrollRun->totalAmount(), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($payrollRun->isDraft())
                    <form method="POST" action="{{ route('payroll.runs.pay', $payrollRun) }}" class="mt-6" onsubmit="return confirm('{{ __('This will record a paid Expense for each person above. Continue?') }}')">
                        @csrf
                        <x-primary-button type="submit">{{ __('Process Payroll — ₦:total', ['total' => number_format($payrollRun->totalAmount(), 2)]) }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
