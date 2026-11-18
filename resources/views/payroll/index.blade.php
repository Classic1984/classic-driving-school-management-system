<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payroll') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'salary-updated')
                <div class="rounded-lg bg-green-50 ring-1 ring-green-200 text-green-800 px-4 py-3 text-sm">{{ __('Salary updated.') }}</div>
            @elseif (session('status') === 'payroll-run-created')
                <div class="rounded-lg bg-green-50 ring-1 ring-green-200 text-green-800 px-4 py-3 text-sm">{{ __('Draft payroll run created — review it below before processing.') }}</div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 ring-1 ring-red-200 text-red-800 px-4 py-3 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Payroll') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Set monthly salaries, then generate and process a payroll run each month') }}</p>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Salary Roster') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Name') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Role') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Monthly Salary') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('On Payroll') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($roster as $person)
                                <tr>
                                    <form method="POST" action="{{ route('payroll.salaries.update') }}" class="contents">
                                        @csrf
                                        <input type="hidden" name="payee_type" value="{{ $person['type'] }}">
                                        <input type="hidden" name="payee_id" value="{{ $person['id'] }}">
                                        <td class="px-4 py-2 text-gray-900 font-medium whitespace-nowrap">{{ $person['name'] }}</td>
                                        <td class="px-4 py-2 text-gray-500 whitespace-nowrap">{{ $person['role_or_status'] }}</td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-1">
                                                <span class="text-gray-500">₦</span>
                                                <input type="number" name="monthly_amount" step="0.01" min="0" value="{{ $person['monthly_amount'] }}" class="w-32 rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <input type="checkbox" name="active" value="1" @checked($person['active']) class="rounded ring-1 ring-gray-300 text-gold-600 focus:ring-gold-500">
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="submit" class="inline-flex items-center rounded-lg ring-1 ring-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">{{ __('Save') }}</button>
                                        </td>
                                    </form>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Generate a Payroll Run') }}</h3>
                <form method="POST" action="{{ route('payroll.runs.store') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label for="month" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('Month') }}</label>
                        <input type="month" name="month" id="month" value="{{ now()->format('Y-m') }}" required class="rounded-lg ring-1 ring-gray-300 border-0 text-sm focus:border-gold-500 focus:ring-gold-500">
                    </div>
                    <x-primary-button type="submit">{{ __('Generate Draft Run') }}</x-primary-button>
                </form>
                <p class="text-xs text-gray-500 mt-2">{{ __('Generating a run only snapshots who\'s active on the roster and what they\'re owed — it doesn\'t move any money. You review and process it on the next page.') }}</p>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">{{ __('Payroll Runs') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Month') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('Status') }}</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-500 uppercase text-xs tracking-wider">{{ __('People') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($runs as $run)
                                <tr>
                                    <td class="px-4 py-2 text-gray-900 font-medium whitespace-nowrap">{{ $run->month->format('F Y') }}</td>
                                    <td class="px-4 py-2"><x-badge :color="$run->isPaid() ? 'green' : 'amber'" class="capitalize">{{ $run->status }}</x-badge></td>
                                    <td class="px-4 py-2 text-gray-500">{{ $run->items_count }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('payroll.runs.show', $run) }}" class="text-sm font-semibold text-gold-700 hover:text-gold-800">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">{{ __('No payroll runs yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
