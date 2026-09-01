<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment Details') }}
        </h2>
    </x-slot>

    @php
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $receiptIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $infoIconPath = 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';

        $statusMeta = match ($payment->status) {
            'paid' => 'green',
            'pending' => 'amber',
            'failed' => 'red',
            'refunded' => 'blue',
            default => 'gray',
        };
    @endphp

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    @if (session('status') === 'payment-reversed')
                        <p class="text-sm font-medium text-green-600 mb-4">{{ __('Payment reversed successfully.') }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-2xl font-extrabold text-gray-900 font-mono truncate">{{ $payment->receipt_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $payment->student->name }}</p>
                        </div>
                        <x-badge :color="$statusMeta" class="capitalize">{{ $payment->status }}</x-badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Date') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $payment->payment_date->format('Y-m-d') }}</p>
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
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Course') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $payment->course->name ?? __('Multiple Services') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Amount') }}</p>
                                <p class="text-sm font-bold text-gray-900">₦{{ number_format($payment->amount, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Payment Method') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Reference Number') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $payment->reference_number ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Recorded By') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $payment->recordedBy?->name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Notes') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $payment->notes ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Allocation Breakdown') }}</h3>
                        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                            <th class="px-3 py-3">{{ __('Charge') }}</th>
                                            <th class="px-3 py-3 text-right">{{ __('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @forelse ($payment->allocations as $allocation)
                                            <tr>
                                                <td class="px-3 py-3 text-sm text-gray-700">{{ $allocation->label() }}</td>
                                                <td class="px-3 py-3 text-sm text-right font-semibold text-gray-900">₦{{ number_format($allocation->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-3 py-4 text-sm text-gray-500">{{ __('No allocation detail recorded for this payment.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($payment->reversal)
                        <div class="mt-6 flex items-start gap-3 rounded-lg bg-blue-50 ring-1 ring-blue-100 p-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
                            </span>
                            <div class="text-sm">
                                <p class="font-bold text-blue-900">{{ __('This payment was reversed.') }}</p>
                                <p class="text-blue-800 mt-1">
                                    {{ $payment->reversal->created_at->format('Y-m-d H:i') }} — {{ __('by') }} {{ $payment->reversal->reversedBy->name }}
                                </p>
                                <p class="text-blue-800 mt-1"><span class="font-semibold">{{ __('Reason') }}:</span> {{ $payment->reversal->reason }}</p>
                                <p class="text-blue-800 mt-1"><span class="font-semibold">{{ __('Amount Reversed') }}:</span> ₦{{ number_format($payment->reversal->amount, 2) }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($payment->corrections->isNotEmpty())
                        <div class="mt-6">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Correction History') }}</h3>
                            <div class="space-y-3">
                                @foreach ($payment->corrections as $correction)
                                    <div class="text-sm rounded-lg bg-gray-50 ring-1 ring-gray-200 p-4">
                                        <p class="text-gray-500">
                                            {{ $correction->created_at->format('Y-m-d H:i') }} — {{ __('by') }} {{ $correction->correctedBy->name }}
                                        </p>
                                        <p class="mt-1"><span class="font-semibold">{{ __('Reason') }}:</span> {{ $correction->reason }}</p>
                                        <div class="mt-2 grid grid-cols-2 gap-4 text-xs">
                                            <div>
                                                <p class="font-semibold text-gray-500 uppercase tracking-wider">{{ __('Original') }}</p>
                                                @foreach ($correction->original_allocations as $row)
                                                    <p>{{ $row['label'] }}: ₦{{ number_format($row['amount'], 2) }}</p>
                                                @endforeach
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-500 uppercase tracking-wider">{{ __('Corrected') }}</p>
                                                @foreach ($correction->new_allocations as $row)
                                                    <p>{{ $row['label'] }}: ₦{{ number_format($row['amount'], 2) }}</p>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-4 mt-6">
                        <a href="{{ route('payments.receipt', $payment) }}">
                            <x-secondary-button type="button">{{ __('View Receipt') }}</x-secondary-button>
                        </a>
                        @if (auth()->user()->isDirector())
                            <a href="{{ route('payments.correct.edit', $payment) }}">
                                <x-secondary-button type="button">{{ __('Correct Allocation') }}</x-secondary-button>
                            </a>
                            <a href="{{ route('payments.edit', $payment) }}">
                                <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                            </a>
                        @endif
                        @if (auth()->user()->isAdmin() && $payment->status === 'paid' && ! $payment->reversal)
                            <a href="{{ route('payments.reverse.create', $payment) }}">
                                <x-secondary-button type="button" class="!text-red-700">{{ __('Reverse Payment') }}</x-secondary-button>
                            </a>
                        @endif
                        <a href="{{ route('payments.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
