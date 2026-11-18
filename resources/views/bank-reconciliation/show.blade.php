<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bank Reconciliation — :start – :end', ['start' => $bankReconciliation->period_start->format('M j'), 'end' => $bankReconciliation->period_end->format('M j, Y')]) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $statusMessages = [
                    'reconciliation-started' => __('Reconciliation started.'),
                    'line-added' => __('Line added.'),
                    'line-matched' => __('Line matched.'),
                    'line-unmatched' => __('Line unmatched.'),
                    'line-ignored' => __('Line marked as ignored.'),
                    'reconciliation-completed' => __('Reconciliation completed.'),
                ];
            @endphp
            @if (session('status'))
                <div class="rounded-lg bg-green-50 ring-1 ring-green-200 text-green-800 px-4 py-3 text-sm">{{ $statusMessages[session('status')] ?? session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded-lg bg-red-50 ring-1 ring-red-200 text-red-800 px-4 py-3 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ $bankReconciliation->period_start->format('M j') }} – {{ $bankReconciliation->period_end->format('M j, Y') }}</h3>
                    <p class="text-sm text-gray-500">
                        <x-badge :color="$bankReconciliation->isCompleted() ? 'green' : 'amber'" class="capitalize">{{ str_replace('_', ' ', $bankReconciliation->status) }}</x-badge>
                        @if ($bankReconciliation->isCompleted())
                            {{ __('Completed by :name on :date', ['name' => $bankReconciliation->completedBy?->name ?? 'Unknown', 'date' => $bankReconciliation->completed_at->format('M j, Y g:i A')]) }}
                        @endif
                    </p>
                    @if ($bankReconciliation->opening_balance !== null || $bankReconciliation->closing_balance !== null)
                        <p class="text-sm text-gray-500 mt-1">
                            @if ($bankReconciliation->opening_balance !== null) {{ __('Opening: ₦:amount', ['amount' => number_format($bankReconciliation->opening_balance, 2)]) }} @endif
                            @if ($bankReconciliation->closing_balance !== null) &nbsp;·&nbsp; {{ __('Closing: ₦:amount', ['amount' => number_format($bankReconciliation->closing_balance, 2)]) }} @endif
                        </p>
                    @endif
                </div>
                <a href="{{ route('bank-reconciliation.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                    {{ __('Back to Reconciliations') }}
                </a>
            </div>

            @if ($bankReconciliation->status === 'in_progress')
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Add Bank Statement Lines') }}</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-2">{{ __('Upload a CSV with columns: date, description, amount (amount signed — positive for money in, negative for money out).') }}</p>
                            <form method="POST" action="{{ route('bank-reconciliation.upload', $bankReconciliation) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <input type="file" name="statement" accept=".csv,.txt" required class="text-sm">
                                <x-secondary-button type="submit">{{ __('Upload CSV') }}</x-secondary-button>
                            </form>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-2">{{ __('Or add one line by hand.') }}</p>
                            <form method="POST" action="{{ route('bank-reconciliation.lines.store', $bankReconciliation) }}" class="flex flex-wrap items-end gap-2">
                                @csrf
                                <input type="date" name="line_date" required class="rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                                <input type="text" name="description" placeholder="{{ __('Description') }}" required class="rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                                <input type="number" step="0.01" name="amount" placeholder="{{ __('Amount') }}" required class="w-28 rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                                <x-secondary-button type="submit">{{ __('Add') }}</x-secondary-button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Bank Statement Lines') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Amount') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($bankReconciliation->lines->sortBy('line_date') as $line)
                                <tr>
                                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ $line->line_date->format('M j, Y') }}</td>
                                    <td class="px-4 py-2 text-gray-900">{{ $line->description }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap {{ $line->isInflow() ? 'text-green-700' : 'text-red-700' }}">₦{{ number_format($line->amount, 2) }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="match($line->status) { 'matched' => 'green', 'ignored' => 'gray', default => 'amber' }" class="capitalize">{{ $line->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        @if ($bankReconciliation->status === 'in_progress')
                                            @if ($line->status === 'unmatched')
                                                <details class="inline-block text-left">
                                                    <summary class="cursor-pointer text-xs font-semibold text-gold-700 hover:text-gold-800">{{ __('Match…') }}</summary>
                                                    <form method="POST" action="{{ route('bank-reconciliation.lines.match', [$bankReconciliation, $line]) }}" class="mt-2 flex items-center gap-2">
                                                        @csrf
                                                        <select name="source" required class="rounded-lg ring-1 ring-gray-300 border-0 text-xs focus:border-gold-500 focus:ring-gold-500">
                                                            <option value="">{{ __('Select…') }}</option>
                                                            @foreach ($unmatchedMovements as $movement)
                                                                <option value="{{ $movement['source_type'] }}|{{ $movement['source_id'] }}">
                                                                    {{ $movement['date']->format('M j') }} — {{ $movement['description'] }} — ₦{{ number_format($movement['amount'], 2) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="text-xs font-semibold text-gold-700 hover:text-gold-800">{{ __('Link') }}</button>
                                                    </form>
                                                </details>
                                                <form method="POST" action="{{ route('bank-reconciliation.lines.ignore', [$bankReconciliation, $line]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-gray-700 ml-2">{{ __('Ignore') }}</button>
                                                </form>
                                            @elseif ($line->status === 'matched')
                                                <form method="POST" action="{{ route('bank-reconciliation.lines.unmatch', [$bankReconciliation, $line]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-gray-700">{{ __('Unmatch') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('bank-reconciliation.lines.unmatch', [$bankReconciliation, $line]) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-gray-500 hover:text-gray-700">{{ __('Reconsider') }}</button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">{{ __('No lines yet — upload a CSV or add one by hand above.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Recorded in the Books, Not Yet Matched to a Bank Line') }}</h3>
                <p class="text-xs text-gray-500 mb-3">{{ __('Every paid payment and paid expense in this period that no bank statement line has been linked to yet.') }}</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Description') }}</th>
                                <th class="px-4 py-2 text-right font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($unmatchedMovements as $movement)
                                <tr>
                                    <td class="px-4 py-2 text-gray-700 whitespace-nowrap">{{ $movement['date']->format('M j, Y') }}</td>
                                    <td class="px-4 py-2 text-gray-900">{{ $movement['description'] }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap {{ $movement['amount'] >= 0 ? 'text-green-700' : 'text-red-700' }}">₦{{ number_format($movement['amount'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ __('Everything recorded in the books for this period is matched.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($bankReconciliation->status === 'in_progress')
                    <form method="POST" action="{{ route('bank-reconciliation.complete', $bankReconciliation) }}" class="mt-6">
                        @csrf
                        <x-primary-button type="submit">{{ __('Complete Reconciliation') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
