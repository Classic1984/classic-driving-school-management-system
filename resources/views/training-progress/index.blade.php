<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Training Progress') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                                <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Student Training Progress') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('Every active enrollment and how far along it is') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 ring-1 ring-amber-200 px-4 py-2 text-sm font-semibold text-amber-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                            {{ $enrollments->total() }} {{ __('Enrollments') }}
                        </span>
                    </div>
                </div>

                <div class="px-6 sm:px-8 pb-6 sm:pb-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                        <th class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                {{ __('Student') }}
                                            </span>
                                        </th>
                                        <th class="px-3 py-3">{{ __('Student ID') }}</th>
                                        <th class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                                                {{ __('Program') }}
                                            </span>
                                        </th>
                                        <th class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                {{ __('Start Date') }}
                                            </span>
                                        </th>
                                        <th class="px-3 py-3">{{ __('Total Days') }}</th>
                                        <th class="px-3 py-3">{{ __('Days Used') }}</th>
                                        <th class="px-3 py-3">{{ __('Days Remaining') }}</th>
                                        <th class="px-3 py-3">{{ __('Expected Completion') }}</th>
                                        <th class="px-3 py-3">{{ __('Completion') }}</th>
                                        <th class="px-3 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                                                {{ __('Status') }}
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($enrollments as $enrollment)
                                        <tr>
                                            <td class="px-3 py-3 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                    </span>
                                                    <a href="{{ route('students.show', $enrollment->student_id) }}" class="font-semibold text-gray-800 hover:text-amber-600">
                                                        {{ $enrollment->student->name }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3 text-sm font-mono text-gray-600">{{ $enrollment->student->student_id_number }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }} / {{ $enrollment->course->totalTrainingDays() }} {{ __('Days') }})</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ optional($enrollment->enrolled_at)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ $enrollment->course->totalTrainingDays() }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ $enrollment->attendedDays() }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ $enrollment->remainingTrainingDays() }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ optional($enrollment->expectedCompletionDate())->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm font-semibold text-gray-900">{{ $enrollment->trainingCompletionPercentage() }}%</td>
                                            <td class="px-3 py-3 text-sm">
                                                @php($label = $enrollment->trainingStatusLabel())
                                                <x-badge :color="match ($label) {
                                                    'Completed' => 'blue',
                                                    'Expired' => 'red',
                                                    default => 'green',
                                                }">{{ __($label) }}</x-badge>
                                                @if ($enrollment->status === 'locked')
                                                    <span class="block text-xs text-gray-500 mt-0.5">{{ $enrollment->lockedReasonLabel() }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-3 py-10 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                                    </span>
                                                    <p class="text-sm text-gray-500">{{ __('No enrollments yet.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
