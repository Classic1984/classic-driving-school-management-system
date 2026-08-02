<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expenses') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Expenses') }}
                    </h3>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('finance.summary') }}" class="text-sm text-gray-600 hover:underline">{{ __('View Finance Summary') }}</a>
                        <a href="{{ route('expenses.create') }}">
                            <x-primary-button type="button">{{ __('Record Expense') }}</x-primary-button>
                        </a>
                    </div>
                </div>

                @if (session('status') === 'expense-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Expense recorded successfully.') }}</p>
                @elseif (session('status') === 'expense-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Expense updated successfully.') }}</p>
                @elseif (session('status') === 'expense-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Expense removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Category') }}</th>
                                <th class="px-4 py-2">{{ __('Amount') }}</th>
                                <th class="px-4 py-2">{{ __('Description') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($expenses as $expense)
                                <tr>
                                    <td class="px-4 py-2">{{ $expense->expense_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</td>
                                    <td class="px-4 py-2">{{ number_format($expense->amount, 2) }}</td>
                                    <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($expense->description, 40) ?: '—' }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('expenses.show', $expense) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('expenses.edit', $expense) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        <form method="post" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this expense?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No expenses recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
