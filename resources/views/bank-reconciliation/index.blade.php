<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bank Reconciliation') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 ring-1 ring-red-200 text-red-800 px-4 py-3 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Bank Reconciliation') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Match the bank statement against the school\'s own recorded payments and expenses for a period') }}</p>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Start a New Reconciliation') }}</h3>
                <form method="POST" action="{{ route('bank-reconciliation.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                    @csrf
                    <div>
                        <label for="period_start" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Period Start') }}</label>
                        <input type="date" name="period_start" id="period_start" required class="w-full rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label for="period_end" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Period End') }}</label>
                        <input type="date" name="period_end" id="period_end" required class="w-full rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label for="opening_balance" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Opening Balance (optional)') }}</label>
                        <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="w-full rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div>
                        <label for="closing_balance" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Closing Balance (optional)') }}</label>
                        <input type="number" step="0.01" name="closing_balance" id="closing_balance" class="w-full rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <div class="sm:col-span-4">
                        <x-primary-button type="submit">{{ __('Start Reconciliation') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Reconciliations') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Period') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Lines') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($reconciliations as $reconciliation)
                                <tr>
                                    <td class="px-4 py-2 text-gray-900 font-medium whitespace-nowrap">{{ $reconciliation->period_start->format('M j') }} – {{ $reconciliation->period_end->format('M j, Y') }}</td>
                                    <td class="px-4 py-2"><x-badge :color="$reconciliation->isCompleted() ? 'green' : 'amber'" class="capitalize">{{ str_replace('_', ' ', $reconciliation->status) }}</x-badge></td>
                                    <td class="px-4 py-2 text-gray-500">{{ $reconciliation->lines_count }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('bank-reconciliation.show', $reconciliation) }}" class="text-sm font-semibold text-gold-700 hover:text-gold-800">{{ __('Open') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">{{ __('No reconciliations yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
