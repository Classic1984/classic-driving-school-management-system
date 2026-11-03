<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $service->name }} {{ __('Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    @php
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clipboardCheckIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $checkCircleIconPath = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $infoIconPath = 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';

        $periodLabels = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'];
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $service->name }} {{ __('Report') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('Complete record of :service', ['service' => Illuminate\Support\Str::lower($service->name)]) }} — {{ __($label) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 print:hidden">
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                                {{ __('Print') }}
                            </button>
                            <a href="{{ route('service-reports.export', ['service' => $service, 'period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-400/30 bg-green-500/10 px-4 py-2 text-sm font-semibold text-green-400 hover:bg-green-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                {{ __('Export Excel') }}
                            </a>
                            <a href="{{ route('service-reports.export-pdf', ['service' => $service, 'period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-400/30 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                                {{ __('Download PDF') }}
                            </a>
                        </div>
                    </div>

                    <div class="relative flex flex-wrap items-center gap-2 mt-6 print:hidden">
                        @foreach ($periodLabels as $value => $tabLabel)
                            <a
                                href="{{ route('service-reports.index', ['service' => $service, 'period' => $value]) }}"
                                class="px-4 py-1.5 text-sm font-semibold rounded-full transition {{ $period === $value ? 'bg-amber-500 text-black' : 'bg-white/5 ring-1 ring-white/10 text-gray-300 hover:text-white' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500/15 via-gray-900 to-gray-900 ring-1 ring-amber-400/30 p-5 mt-6 max-w-sm">
                        <svg class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                        <div class="relative flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                            </span>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-300">{{ $service->name }} {{ __('Completed') }}</p>
                        </div>
                        <p class="relative text-4xl font-extrabold text-amber-400 mt-3">{{ $completed->count() }}</p>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-black">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">{{ __('Student Name') }}</th>
                                        <th class="px-4 py-3">{{ __('Charged Date') }}</th>
                                        <th class="px-4 py-3">{{ __('Completed Date') }}</th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($completed as $studentService)
                                        @php $serviceInitials = collect(explode(' ', $studentService->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''); @endphp
                                        <tr class="hover:bg-amber-50/40 transition">
                                            <td class="px-4 py-3 text-sm align-top">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 font-mono text-xs font-bold">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top">
                                                <a href="{{ route('students.show', $studentService->student_id) }}" class="group flex items-center gap-2.5">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-800">{{ $serviceInitials }}</span>
                                                    <span class="font-semibold text-gray-900 group-hover:text-amber-600">{{ $studentService->student->name }}</span>
                                                </a>
                                            </td>
                                                            <td class="px-4 py-3 text-sm align-top text-gray-600">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                    {{ $studentService->created_at->format('l, M j, Y') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top text-gray-600">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                    {{ $studentService->updated_at->format('l, M j, Y') }}
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

                    <div class="flex items-start gap-3 rounded-lg bg-gray-50 ring-1 ring-gray-200 p-4 mt-6">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
                        </span>
                        <p class="text-sm text-gray-600">{{ __('This report shows all :service charges completed :period.', ['service' => $service->name, 'period' => Illuminate\Support\Str::lower($label)]) }}</p>
                    </div>
                </div>

                <div class="relative overflow-hidden border-t border-gray-100 bg-gray-50/60 px-6 sm:px-8 py-5">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M3 9l12 12M3 15l6 6" /></svg>
                    <div class="relative flex items-center gap-3">
                        <x-application-logo class="h-8 w-8 shrink-0 object-contain" />
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                            <p class="text-xs text-gray-500">{{ __('Training Safe Drivers, Building Better Roads.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
