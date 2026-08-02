<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payments') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Payment Records') }}
                    </h3>

                    <a href="{{ route('payments.create') }}">
                        <x-primary-button type="button">{{ __('Record Payment') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'payment-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment recorded successfully.') }}</p>
                @elseif (session('status') === 'payment-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment updated successfully.') }}</p>
                @elseif (session('status') === 'payment-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment record removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Amount') }}</th>
                                <th class="px-4 py-2">{{ __('Method') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="px-4 py-2">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $payment->student->name }}</td>
                                    <td class="px-4 py-2">{{ $payment->course->name }}</td>
                                    <td class="px-4 py-2">{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $payment->status }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('payments.show', $payment) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('payments.edit', $payment) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('payments.destroy', $payment) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this payment record?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No payments recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
