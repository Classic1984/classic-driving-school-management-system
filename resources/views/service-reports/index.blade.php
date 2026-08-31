<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $service->name }} {{ __('Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    @php
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clipboardCheckIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $checkCircleIconPath = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $infoIconPath = 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';

        $periodLabels = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'];
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ $service->name }} {{ __('Report') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Complete record of :service', ['service' => Illuminate\Support\Str::lower($service->name)]) }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white ring-1 ring-gray-200">
                    <svg class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($periodLabels as $value => $tabLabel)
                        <a
                            href="{{ route('service-reports.index', ['service' => $service, 'period' => $value]) }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg text-sm font-semibold transition {{ $period === $value ? 'bg-black text-amber-400' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            {{ __($tabLabel) }}
                        </a>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-amber-400 hover:bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                        {{ __('Print') }}
                    </button>
                    <a href="{{ route('service-reports.export', ['service' => $service, 'period' => $period]) }}">
                        <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('service-reports.export-pdf', ['service' => $service, 'period' => $period]) }}">
                        <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                    </a>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-black p-6 sm:p-8">
                <svg class="pointer-events-none absolute -right-6 -bottom-6 h-40 w-40 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                <div class="relative flex items-center gap-5">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/5 text-amber-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-400">{{ __($label) }} {{ __('Total') }}</p>
                        <div class="flex flex-col-reverse">
                            <p class="text-sm text-gray-300 mt-1">{{ $service->name }} {{ __('Completed') }}</p>
                            <p class="text-4xl font-extrabold text-amber-400">{{ $completed->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">{{ __('Student Name') }}</th>
                                <th class="px-4 py-3">{{ __('Charged Date') }}</th>
                                <th class="px-4 py-3">{{ __('Completed Date') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($completed as $studentService)
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 font-mono text-xs font-bold">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                            </span>
                                            <a href="{{ route('students.show', $studentService->student_id) }}" class="font-semibold text-gray-900 hover:text-amber-600">{{ $studentService->student->name }}</a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                            {{ $studentService->created_at->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                            {{ $studentService->updated_at->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <x-badge color="green" class="inline-flex items-center gap-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkCircleIconPath }}" /></svg>
                                            {{ __('Completed') }}
                                        </x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                            </span>
                                            <p class="text-sm text-gray-500">{{ __('No :service charges were completed during this period.', ['service' => $service->name]) }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-start gap-3 rounded-lg bg-gray-50 ring-1 ring-gray-200 p-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
                </span>
                <p class="text-sm text-gray-600">{{ __('This report shows all :service charges completed :period.', ['service' => $service->name, 'period' => Illuminate\Support\Str::lower($label)]) }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
