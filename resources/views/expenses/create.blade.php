<x-app-layout>
    @php
        // Arriving here via the "Investment Return" nav/dashboard shortcut
        // pre-selects the category through the query string - the page
        // greets the Director with a matching title so it's obvious they
        // landed in the right place, without needing a whole separate form.
        $prefillCategory = old('category', request('category'));
        $isInvestmentReturn = $prefillCategory === 'investment_return';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $isInvestmentReturn ? __('Record Investment Return') : __('Record Expense') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" />
                    </svg>

                    <div class="relative flex items-center gap-4 min-w-0">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" />
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white truncate">{{ $isInvestmentReturn ? __('Record Investment Return') : __('Record Expense') }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $isInvestmentReturn ? __('This amount will be added to income.') : __('Log money spent running the school') }}</p>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('expenses.store') }}" class="p-4 sm:p-8 space-y-6" enctype="multipart/form-data">
                    @csrf

                    @include('expenses.partials.form-fields')

                    <div class="flex items-center gap-4 pt-4 border-t border-line">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy hover:bg-navy-700 px-5 py-3 text-sm font-bold text-gold-400 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            {{ __('Save') }}
                        </button>
                        <a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-line hover:bg-paper px-5 py-3 text-sm font-semibold text-gray-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
