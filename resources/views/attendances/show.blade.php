<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Login Details') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clockIconPath = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $boltIconPath = 'M3.75 13.5 13.5 3l-1.5 7.5h8.25L10.5 21l1.5-7.5H3.75Z';
        $vehicleIconPath = 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';

        $statusMeta = match ($attendance->status) {
            'present' => 'green',
            'absent' => 'red',
            'late' => 'amber',
            'excused' => 'blue',
            default => 'gray',
        };
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ $attendance->student->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $attendance->date->format('l, j F Y') }}</p>
                        </div>
                        <x-badge :color="$statusMeta" class="capitalize">{{ $attendance->status }}</x-badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Date') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->date->format('Y-m-d') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Time') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Session') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->sessionPeriod() ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Student') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->student->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Course') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->course->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $boltIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Type') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ $attendance->type ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Instructor') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->instructor?->name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $vehicleIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Vehicle') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->vehicle?->name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Duration') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->duration ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                                <x-badge :color="$statusMeta" class="capitalize mt-0.5">{{ $attendance->status }}</x-badge>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Notes') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $attendance->notes ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        @if (auth()->user()->canManageCourses())
                            <a href="{{ route('attendances.edit', $attendance) }}">
                                <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                            </a>
                        @endif
                        <a href="{{ route('attendances.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
