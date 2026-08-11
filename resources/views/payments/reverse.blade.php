<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reverse Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <div class="rounded-md bg-red-50 border border-red-200 p-4">
                    <p class="text-sm text-red-800">
                        {{ __('This does not delete the payment. It stays on file, but is marked reversed and its amount no longer counts toward any balance.') }}
                    </p>
                </div>

                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Receipt No.') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $payment->receipt_number }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $payment->description() }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Amount to Reverse') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">₦{{ number_format($payment->amount, 2) }}</dd>
                    </div>
                </dl>

                <form method="post" action="{{ route('payments.reverse.store', $payment) }}">
                    @csrf

                    <x-input-label for="reason" :value="__('Reason for Reversal')" />
                    <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('reason')" />

                    <div class="flex items-center gap-4 mt-4">
                        <x-primary-button class="!bg-red-700">{{ __('Confirm Reversal') }}</x-primary-button>
                        <a href="{{ route('payments.show', $payment) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
