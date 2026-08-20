<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expense Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Category') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Amount') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ number_format($expense->amount, 2) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Expense Date') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $expense->expense_date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $expense->description ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Receipt Photo') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @if ($expense->receipt_photo_path)
                                <a href="{{ Storage::url($expense->receipt_photo_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ Storage::url($expense->receipt_photo_path) }}" alt="{{ __('Receipt') }}" class="h-24 w-24 object-cover rounded-md border border-gray-200 hover:opacity-80">
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="flex items-center gap-4">
                    <a href="{{ route('expenses.edit', $expense) }}">
                        <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
