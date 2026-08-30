<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payments') }}
        </h2>
    </x-slot>

    @php
        $statusAccent = [
            'paid' => ['color' => 'green', 'border' => 'border-green-500'],
            'pending' => ['color' => 'amber', 'border' => 'border-amber-500'],
            'failed' => ['color' => 'red', 'border' => 'border-red-500'],
            'refunded' => ['color' => 'blue', 'border' => 'border-blue-500'],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Payments') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Track and manage every payment recorded in the system') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('payments.export', ['period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('Export CSV') }}
                    </a>
                    <a href="{{ route('payments.record.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Record Payment') }}
                    </a>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
                @if (auth()->user()->isDirector())
                    <div class="flex items-center gap-2">
                        @foreach (['week' => 'This Week', 'month' => 'This Month', 'all_time' => 'All Time'] as $value => $tabLabel)
                            <a
                                href="{{ route('payments.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm font-semibold rounded-lg {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('payments.create') }}" class="text-sm font-medium text-amber-600 hover:underline">{{ __('Classic single-course form') }}</a>
            </div>

            @if (session('status') === 'payment-created')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment recorded successfully.') }}</p>
            @elseif (session('status') === 'payment-updated')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment updated successfully.') }}</p>
            @elseif (session('status') === 'payment-deleted')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Payment record removed successfully.') }}</p>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="flex items-center gap-4 rounded-xl bg-black text-amber-400 p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-400/10">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider">{{ __("Today's Total") }}</p>
                        <p class="text-2xl font-extrabold mt-0.5">₦{{ number_format($todayTotal, 2) }}</p>
                    </div>
                </div>
                @if (auth()->user()->isDirector())
                    <div class="flex items-center gap-4 rounded-xl bg-amber-500 text-black p-5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-black/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider">{{ __($periodLabel) }} {{ __('Total') }}</p>
                            <p class="text-2xl font-extrabold mt-0.5">₦{{ number_format($periodTotal, 2) }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Payment Records') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                        {{ __('Date') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                        {{ __('Receipt') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        {{ __('Student') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Description') }}</th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                                        {{ __('Amount') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Method') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3">{{ __('Recorded By') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($payments as $payment)
                                @php
                                    $accent = $statusAccent[$payment->status] ?? ['color' => 'gray', 'border' => 'border-gray-300'];
                                    $initials = collect(explode(' ', $payment->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr class="border-l-4 {{ $accent['border'] }}">
                                    <td class="px-3 py-3 text-sm align-top text-gray-700">{{ $payment->payment_date->format('M j, Y') }}</td>
                                    <td class="px-3 py-3 text-xs font-mono align-top text-gray-500">{{ $payment->receipt_number }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400 text-xs font-bold">{{ $initials }}</span>
                                            <span class="font-semibold text-gray-800">{{ $payment->student->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $payment->description() }}</td>
                                    <td class="px-3 py-3 text-sm align-top font-semibold text-gray-800">₦{{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-3 py-3 text-sm align-top capitalize text-gray-600">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$accent['color']" class="capitalize">{{ $payment->status }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $payment->recordedBy?->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-44 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('payments.show', $payment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                <a href="{{ route('payments.receipt', $payment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Receipt') }}</a>
                                                @if (auth()->user()->isDirector())
                                                    <a href="{{ route('payments.edit', $payment) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                @endif
                                                @if (auth()->user()->isAdmin())
                                                    @if ($payment->status === 'paid' && ! $payment->reversal)
                                                        <a href="{{ route('payments.reverse.create', $payment) }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Reverse') }}</a>
                                                    @endif
                                                    <form method="post" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this payment record?') }}');">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
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
