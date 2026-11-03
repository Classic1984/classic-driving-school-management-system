<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reverse Payment') }}
        </h2>
    </x-slot>

    @php
        $arrowUturnIconPath = 'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3';
        $receiptIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-1.519-3.75L12 17.25m0 0-1.481-3.75M12 17.25V21m-7.5-3.75h15A2.25 2.25 0 0 0 21 15V6.75A2.25 2.25 0 0 0 18.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v8.5a2.25 2.25 0 0 0 2.25 2.25Z';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
    @endphp

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6 px-4 sm:px-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-50">
                    <svg class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowUturnIconPath }}" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ __('Reverse Payment') }}</h3>
                    <p class="text-sm font-mono text-gray-500 truncate">{{ $payment->receipt_number }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-6">
                <div class="rounded-xl bg-red-50 ring-1 ring-red-200 p-4">
                    <p class="text-sm text-red-800">
                        {{ __('This does not delete the payment. It stays on file, but is marked reversed and its amount no longer counts toward any balance.') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Receipt No.') }}</p>
                            <p class="text-sm font-bold font-mono text-gray-900">{{ $payment->receipt_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Student') }}</p>
                            <p class="text-sm font-bold text-gray-900">{{ $payment->student->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Description') }}</p>
                            <p class="text-sm font-bold text-gray-900">{{ $payment->description() }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Amount to Reverse') }}</p>
                            <p class="text-sm font-bold text-gray-900">₦{{ number_format($payment->amount, 2) }}</p>
                        </div>
                    </div>
                </div>

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
