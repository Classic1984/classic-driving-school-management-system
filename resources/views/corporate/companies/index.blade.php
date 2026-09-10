<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Companies') }}
        </h2>
    </x-slot>

    @php
        $buildingIconPath = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $searchIconPath = 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z';
        $cogIconPath = 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a7.65 7.65 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    @endphp

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('Companies') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('Corporate clients who send staff or drivers for training') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('corporate-invoice-settings.edit') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cogIconPath }}" /></svg>
                                {{ __('Invoice Settings') }}
                            </a>
                            <a href="{{ route('corporate-companies.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-400 px-5 py-2.5 text-sm font-bold text-black transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                                {{ __('Add Company') }}
                            </a>
                        </div>
                    </div>

                    <form method="get" class="relative mt-6 max-w-sm">
                        <span class="pointer-events-none absolute left-0 top-0 flex h-full w-11 items-center justify-center text-gray-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $searchIconPath }}" /></svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search companies…') }}" class="block w-full pl-11 rounded-lg border-0 bg-white/10 text-white placeholder-gray-400 ring-1 ring-white/15 focus:ring-amber-400 focus:bg-white/15">
                    </form>
                </div>

                @if (session('status') === 'company-created')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Company added successfully.') }}</p>
                @elseif (session('status') === 'company-updated')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Company updated successfully.') }}</p>
                @elseif (session('status') === 'company-deleted')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Company deleted successfully.') }}</p>
                @elseif (session('status') === 'company-in-use')
                    <p class="px-8 pt-6 text-sm font-medium text-red-600">{{ __('This company already has quotations or invoices, so it cannot be deleted.') }}</p>
                @endif

                <div class="p-6 sm:p-8">
                    @if ($companies->isEmpty())
                        <div class="flex flex-col items-center gap-2 py-10">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $buildingIconPath }}" /></svg>
                            </span>
                            <p class="text-sm text-gray-500">
                                {{ $search !== '' ? __('No companies match ":search".', ['search' => $search]) : __('No companies registered yet.') }}
                            </p>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-black">
                                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                            <th class="px-4 py-3">{{ __('Company') }}</th>
                                            <th class="px-4 py-3">{{ __('Contact Person') }}</th>
                                            <th class="px-4 py-3">{{ __('Phone / Email') }}</th>
                                            <th class="px-4 py-3">{{ __('Quotations') }}</th>
                                            <th class="px-4 py-3">{{ __('Invoices') }}</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach ($companies as $company)
                                            <tr class="hover:bg-amber-50/40 transition">
                                                <td class="px-4 py-3 text-sm">
                                                    <a href="{{ route('corporate-companies.show', $company) }}" class="font-semibold text-gray-900 hover:text-amber-600">{{ $company->name }}</a>
                                                    <p class="text-xs font-mono text-gray-400">{{ $company->company_reference }}</p>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $company->contact_person ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">
                                                    <p>{{ $company->phone ?? '—' }}</p>
                                                    <p class="text-gray-400">{{ $company->email ?? '' }}</p>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $company->quotations_count }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $company->invoices_count }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <a href="{{ route('corporate-companies.edit', $company) }}" class="text-sm font-semibold text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            {{ $companies->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
