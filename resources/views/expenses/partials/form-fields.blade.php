@php($expense = $expense ?? null)

<div>
    <x-input-label for="category" :value="__('Category')" />
    <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a category') }}</option>
        @foreach (\App\Models\Expense::CATEGORIES as $value => $label)
            <option value="{{ $value }}" @selected(old('category', $expense?->category) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('category')" />
</div>

<div>
    <x-input-label for="amount" :value="__('Amount')" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $expense?->amount)" required />
    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
</div>

<div>
    <x-input-label for="expense_date" :value="__('Expense Date')" />
    <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', optional($expense?->expense_date)->format('Y-m-d') ?? now()->toDateString())" required />
    <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
</div>

<div>
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $expense?->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>
