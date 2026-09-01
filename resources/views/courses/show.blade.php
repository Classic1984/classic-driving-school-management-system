<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Details') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $boltIconPath = 'M3.75 13.5 13.5 3l-1.5 7.5h8.25L10.5 21l1.5-7.5H3.75Z';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clockIconPath = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ $course->name }}</h3>
                            <p class="text-sm text-gray-500 capitalize">{{ $course->course_type }}</p>
                        </div>
                        <x-badge :color="$course->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $course->status }}</x-badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Description') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $course->description ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $boltIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Course Type') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ $course->course_type }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Schedule') }}</p>
                                <x-badge :color="$course->isWeekend() ? 'blue' : 'gray'" class="capitalize mt-0.5">{{ $course->schedule }}</x-badge>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Duration') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $course->duration_hours }} {{ __('hours') }} ({{ $course->duration_weeks }} {{ __('weeks') }})</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Payment Grace Period') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $course->gracePeriodDays() }} {{ __('days') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Fee') }}</p>
                                <p class="text-sm font-bold text-gray-900">₦{{ number_format($course->fee, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">{{ __('Instructors') }}</p>
                                @forelse ($course->instructors as $courseInstructor)
                                    <p class="text-sm font-bold text-gray-900">{{ $courseInstructor->name }}</p>
                                @empty
                                    <p class="text-sm font-bold text-gray-900">—</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        @if (auth()->user()->canManageCourses())
                            <a href="{{ route('courses.edit', $course) }}">
                                <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                            </a>
                        @endif
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                        </span>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Students') }}</h3>
                    </div>

                    @if (session('status') === 'enrollment-completed')
                        <p class="mt-3 text-sm font-medium text-green-600">{{ __('Course marked as completed.') }}</p>
                    @endif
                    <x-input-error class="mt-3" :messages="$errors->get('enrollment')" />
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
                                                {{ __('Name') }}
                                            </span>
                                        </th>
                                        <th class="px-3 py-3">{{ __('Balance') }}</th>
                                        <th class="px-3 py-3">{{ __('Due Date') }}</th>
                                        <th class="px-3 py-3">{{ __('Status') }}</th>
                                        <th class="px-3 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($course->students as $enrolledStudent)
                                        <tr>
                                            <td class="px-3 py-3 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                    </span>
                                                    <a href="{{ route('students.show', $enrolledStudent) }}" class="font-semibold text-gray-800 hover:text-amber-600">{{ $enrolledStudent->name }}</a>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3 text-sm text-gray-600">₦{{ number_format($enrolledStudent->pivot->balance(), 2) }}</td>
                                            <td class="px-3 py-3 text-sm text-gray-600">{{ optional($enrolledStudent->pivot->due_date)->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-3 py-3 text-sm">
                                                <x-badge :color="match ($enrolledStudent->pivot->statusLabel()) {
                                                    'Registered' => 'gray',
                                                    'Locked' => 'red',
                                                    'Completed' => 'blue',
                                                    'Certified' => 'amber',
                                                    default => 'green',
                                                }">{{ __($enrolledStudent->pivot->statusLabel()) }}</x-badge>
                                                @if ($enrolledStudent->pivot->status === 'locked')
                                                    <span class="block text-xs text-gray-500 mt-0.5">{{ $enrolledStudent->pivot->lockedReasonLabel() }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-sm">
                                                @if ($enrolledStudent->pivot->status !== 'completed' && $enrolledStudent->pivot->hasCompletedTraining() && $enrolledStudent->pivot->balance() <= 0)
                                                    <form method="post" action="{{ route('enrollments.complete', $enrolledStudent->pivot->id) }}" class="inline">
                                                        @csrf
                                                        @method('patch')
                                                        <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Mark Complete') }}</button>
                                                    </form>
                                                @endif
                                                @if ($enrolledStudent->pivot->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                                                    <a href="{{ route('enrollments.reactivate.create', $enrolledStudent->pivot->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Reactivate') }}</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-10 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                                    </span>
                                                    <p class="text-sm text-gray-500">{{ __('No students enrolled yet.') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
