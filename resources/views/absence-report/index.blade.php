<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absence Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    @php
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $userMinusIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $clipboardXIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9.75 12.75l1.5 1.5 3-3';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $printerIconPath = 'M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.055 48.055 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z';
        $exclamationTriangleIconPath = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';

        $studentsAbsentCount = $attendances->pluck('student_id')->unique()->count();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-red-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $exclamationTriangleIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-500/15 text-red-400 ring-1 ring-red-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $userMinusIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('Absence Report') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('Track student absences over time') }} — {{ __($label) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 print:hidden">
                            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-white/10 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $printerIconPath }}" /></svg>
                                {{ __('Print') }}
                            </button>
                            <a href="{{ route('absence-report.export', ['period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-green-400/30 bg-green-500/10 px-4 py-2 text-sm font-semibold text-green-400 hover:bg-green-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                                {{ __('Export Excel') }}
                            </a>
                            <a href="{{ route('absence-report.export-pdf', ['period' => $period]) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-red-400/30 bg-red-500/10 px-4 py-2 text-sm font-semibold text-red-400 hover:bg-red-500/20 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L13.5 15.75m0 0-2.25 2.25M13.5 15.75l2.25 2.25M13.5 15.75l-2.25-2.25M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
                                {{ __('Download PDF') }}
                            </a>
                        </div>
                    </div>

                    <div class="relative inline-flex items-center gap-1 rounded-full bg-white/5 ring-1 ring-white/10 p-1 mt-6 print:hidden">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $value => $tabLabel)
                            <a
                                href="{{ route('absence-report.index', ['period' => $value]) }}"
                                class="px-4 py-1.5 text-sm font-semibold rounded-full transition {{ $period === $value ? 'bg-amber-500 text-black' : 'text-gray-300 hover:text-white' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="relative grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-red-500/15 via-gray-900 to-gray-900 ring-1 ring-red-400/30 p-5">
                            <svg class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 text-red-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $userMinusIconPath }}" /></svg>
                            <div class="relative flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-500/15 text-red-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $userMinusIconPath }}" /></svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-300">{{ __('Students Absent') }}</p>
                            </div>
                            <p class="relative text-4xl font-extrabold text-red-400 mt-3">{{ $studentsAbsentCount }}</p>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500/15 via-gray-900 to-gray-900 ring-1 ring-amber-400/30 p-5">
                            <svg class="pointer-events-none absolute -right-4 -bottom-4 h-24 w-24 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardXIconPath }}" /></svg>
                            <div class="relative flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardXIconPath }}" /></svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-300">{{ __('Absences Logged') }}</p>
                            </div>
                            <p class="relative text-4xl font-extrabold text-amber-400 mt-3">{{ $attendances->count() }}</p>
                        </div>
                    </div>

                    @if ($attendancesByDate->count() === 1)
                        <div class="relative flex items-center gap-3 rounded-2xl bg-white/5 ring-1 ring-white/10 px-5 py-4 mt-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-gray-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            </span>
                            <div>
                                <p class="font-bold text-white whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($attendancesByDate->keys()->first())->format('l, M j, Y') }}</p>
                                <p class="text-sm text-gray-400">{{ trans_choice('{0} :count students|{1} :count student|[2,*] :count students', $studentsAbsentCount, ['count' => $studentsAbsentCount]) }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-6 sm:p-8">
                    @forelse ($attendancesByDate as $date => $dayAttendances)
                        <div class="mb-6 last:mb-0">
                            @if ($attendancesByDate->count() > 1)
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-red-400"></span>
                                    <h4 class="text-sm font-semibold text-gray-800">
                                        {{ \Illuminate\Support\Carbon::parse($date)->format('l, M j, Y') }}
                                        <span class="font-normal text-gray-500">&middot; {{ trans_choice('{0} :count students|{1} :count student|[2,*] :count students', $dayAttendances->count(), ['count' => $dayAttendances->count()]) }}</span>
                                    </h4>
                                </div>
                            @endif
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-black">
                                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                                <th class="px-4 py-3">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                        {{ __('Student ID') }}
                                                    </span>
                                                </th>
                                                <th class="px-4 py-3">{{ __('Student Name') }}</th>
                                                <th class="px-4 py-3">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                                                        {{ __('Course') }}
                                                    </span>
                                                </th>
                                                <th class="px-4 py-3">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                                                        {{ __('Training Status') }}
                                                    </span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($dayAttendances as $attendance)
                                                @php
                                                    $absenceInitials = collect(explode(' ', $attendance->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                                @endphp
                                                <tr class="hover:bg-red-50/40 transition">
                                                    <td class="px-4 py-3">
                                                        <span class="font-mono text-sm text-gray-700">{{ $attendance->student->student_id_number }}</span>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm">
                                                        <a href="{{ route('students.show', $attendance->student_id) }}" class="group flex items-center gap-2.5">
                                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-bold text-red-700">{{ $absenceInitials }}</span>
                                                            <span class="font-semibold text-gray-900 group-hover:text-amber-600 print:text-gray-900">{{ $attendance->student->name }}</span>
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $attendance->course->name }}</td>
                                                    <td class="px-4 py-3 text-sm">
                                                        @php $trainingStatus = $enrollmentStatuses["{$attendance->student_id}:{$attendance->course_id}"] ?? null; @endphp
                                                        @if ($trainingStatus)
                                                            <x-badge :color="match ($trainingStatus) {
                                                                'Completed' => 'blue',
                                                                'Expired' => 'red',
                                                                default => 'green',
                                                            }">{{ $trainingStatus }}</x-badge>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center gap-3 px-4 py-12 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-green-50 text-green-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                            </span>
                            <p class="text-sm font-semibold text-gray-700">{{ __('No students were absent during this period.') }}</p>
                        </div>
                    @endforelse
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
