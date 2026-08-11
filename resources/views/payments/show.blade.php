<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Receipt No.') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $payment->receipt_number }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Date') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->payment_date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->course->name ?? __('Multiple Services') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Amount') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ number_format($payment->amount, 2) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Payment Method') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <x-badge :color="match ($payment->status) {
                                'paid' => 'green',
                                'pending' => 'amber',
                                'failed' => 'red',
                                'refunded' => 'blue',
                                default => 'gray',
                            }" class="capitalize">{{ $payment->status }}</x-badge>
                        </dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Reference Number') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->reference_number ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Notes') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->notes ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Recorded By') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->recordedBy?->name ?? '—' }}</dd>
                    </div>
                </dl>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Allocation Breakdown') }}</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-2 py-1">{{ __('Charge') }}</th>
                                <th class="px-2 py-1 text-right">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($payment->allocations as $allocation)
                                <tr>
                                    <td class="px-2 py-1 text-sm">{{ $allocation->label() }}</td>
                                    <td class="px-2 py-1 text-sm text-right">{{ number_format($allocation->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-2 py-2 text-sm text-gray-500">{{ __('No allocation detail recorded for this payment.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('payments.receipt', $payment) }}">
                        <x-secondary-button type="button">{{ __('View Receipt') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('payments.edit', $payment) }}">
                        <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('payments.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
