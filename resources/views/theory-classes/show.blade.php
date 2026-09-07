<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Class') }} — {{ $theoryClass->class_date->format('l, M j, Y') }}
        </h2>
    </x-slot>

    @php
        $bookIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $checkCircleIconPath = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $xCircleIconPath = 'M9.75 9.75l4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $chartBarIconPath = 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'theory-class-updated')
                <p class="text-sm font-medium text-green-600">{{ __('Class details updated.') }}</p>
            @elseif (session('status') === 'theory-attendance-saved')
                <p class="text-sm font-medium text-green-600">{{ __('Attendance saved.') }}</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-20 w-20 text-gray-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                    <div class="relative flex items-stretch gap-3">
                        <span class="w-1 shrink-0 rounded-full bg-gray-400"></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ __('Expected') }}</p>
                            <p class="mt-1 text-2xl font-extrabold text-gray-800">{{ $roster->count() }}</p>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-green-200 bg-green-50/60 p-4">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-20 w-20 text-green-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkCircleIconPath }}" /></svg>
                    <div class="relative flex items-stretch gap-3">
                        <span class="w-1 shrink-0 rounded-full bg-green-400"></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-green-700">{{ __('Present') }}</p>
                            <p class="mt-1 text-2xl font-extrabold text-green-700">{{ $theoryClass->presentCount() }}</p>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-red-200 bg-red-50/60 p-4">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-20 w-20 text-red-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $xCircleIconPath }}" /></svg>
                    <div class="relative flex items-stretch gap-3">
                        <span class="w-1 shrink-0 rounded-full bg-red-400"></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-red-700">{{ __('Absent') }}</p>
                            <p class="mt-1 text-2xl font-extrabold text-red-700">{{ $theoryClass->absentCount() }}</p>
                        </div>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50/60 p-4">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-20 w-20 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chartBarIconPath }}" /></svg>
                    <div class="relative flex items-stretch gap-3">
                        <span class="w-1 shrink-0 rounded-full bg-amber-400"></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-amber-700">{{ __('Attendance') }}</p>
                            <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $theoryClass->attendancePercentage() }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookIconPath }}" /></svg>
                        </span>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Class Details') }}</h3>
                    </div>

                    @if (auth()->user()->canManageCourses())
                        <form method="post" action="{{ route('theory-classes.update', $theoryClass) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                            @csrf
                            @method('patch')

                            <div class="flex-1 min-w-[16rem]">
                                <x-input-label for="topic" :value="__('Topic Taught')" />
                                <x-text-input id="topic" name="topic" type="text" class="mt-1 block w-full" :value="old('topic', $theoryClass->topic)" />
                                <x-input-error class="mt-2" :messages="$errors->get('topic')" />
                            </div>

                            <div>
                                <x-input-label for="instructor_id" :value="__('Instructor')" />
                                <select id="instructor_id" name="instructor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                    <option value="">{{ __('— None —') }}</option>
                                    @foreach ($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" @selected(old('instructor_id', $theoryClass->instructor_id) == $instructor->id)>{{ $instructor->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('instructor_id')" />
                            </div>

                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" />
                                <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block" :value="old('start_time', $theoryClass->start_time)" />
                                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
                            </div>

                            <div class="w-full">
                                <x-input-label for="notes" :value="__('Class Notes')" />
                                <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('notes', $theoryClass->notes) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                            </div>

                            <div class="w-full">
                                <x-input-label for="materials" :value="__('Lecture Material (PDF, Word, PowerPoint, or image)')" />
                                <input id="materials" name="materials" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,image/*" class="mt-1 block w-full text-sm text-gray-700 file:me-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-gray-100 file:text-sm file:font-medium hover:file:bg-gray-200">
                                <x-input-error class="mt-2" :messages="$errors->get('materials')" />
                                @if ($theoryClass->materials_path)
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ __('Current file') }}:
                                        <a href="{{ $theoryClass->materialsUrl() }}" target="_blank" class="text-amber-600 hover:underline">{{ $theoryClass->materials_original_name ?: __('View') }}</a>
                                        — {{ __('uploading a new file replaces it') }}
                                    </p>
                                @endif
                            </div>

                            <x-primary-button>{{ __('Save Details') }}</x-primary-button>
                        </form>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Topic') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryClass->topic ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Instructor') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryClass->instructor?->name ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Notes') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryClass->notes ?: '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Lecture Material') }}</p>
                                    <p class="text-sm font-bold text-gray-900">
                                        @if ($theoryClass->materials_path)
                                            <a href="{{ $theoryClass->materialsUrl() }}" target="_blank" class="text-amber-600 hover:underline">{{ $theoryClass->materials_original_name ?: __('View') }}</a>
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8 pb-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                        </span>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Roster') }}</h3>
                    </div>
                </div>

                <div class="px-6 sm:px-8 pb-6 sm:pb-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                        <th class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                {{ __('Student') }}
                                            </span>
                                        </th>
                                        <th class="px-4 py-3">{{ __('Status') }}</th>
                                        <th class="px-4 py-3">{{ __('Score') }}</th>
                                        <th class="px-4 py-3">{{ __('Remarks') }}</th>
                                        @if (auth()->user()->canManageCourses())
                                            <th class="px-4 py-3"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($roster as $entry)
                                        <tr x-data="{ editing: false }">
                                            <td class="px-4 py-3 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                    </span>
                                                    <span class="font-semibold text-gray-800">{{ $entry['student']->name }}</span>
                                                </div>
                                            </td>

                                            @if (auth()->user()->canManageCourses())
                                                <td class="px-4 py-3" colspan="3" x-show="!editing">
                                                    <div class="flex items-center gap-3">
                                                        @if ($entry['attendance'])
                                                            <x-badge :color="match ($entry['attendance']->status) {
                                                                'present' => 'green',
                                                                'absent' => 'red',
                                                                'late' => 'amber',
                                                                'excused' => 'blue',
                                                                default => 'gray',
                                                            }" class="capitalize">{{ $entry['attendance']->status }}</x-badge>
                                                            <span class="text-sm text-gray-500">{{ $entry['attendance']->score !== null ? __('Score: ') . $entry['attendance']->score : '' }}</span>
                                                            <span class="text-sm text-gray-500">{{ $entry['attendance']->remarks }}</span>
                                                        @else
                                                            <x-badge color="gray">{{ __('Not yet marked') }}</x-badge>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-right" x-show="!editing">
                                                    <button type="button" @click="editing = true" class="text-sm text-amber-600 hover:underline">
                                                        {{ $entry['attendance'] ? __('Edit') : __('Mark') }}
                                                    </button>
                                                </td>

                                                <td class="px-4 py-3" colspan="4" x-show="editing">
                                                    <form method="post" action="{{ route('theory-classes.attendances.store', $theoryClass) }}" class="flex flex-wrap items-end gap-3">
                                                        @csrf
                                                        <input type="hidden" name="student_id" value="{{ $entry['student']->id }}">

                                                        <div>
                                                            <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                                @foreach (['present', 'absent', 'late', 'excused'] as $status)
                                                                    <option value="{{ $status }}" @selected(($entry['attendance']->status ?? 'present') === $status)>{{ ucfirst($status) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <input type="number" name="score" min="0" max="100" placeholder="{{ __('Score') }}" value="{{ $entry['attendance']->score ?? '' }}" class="w-20 rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div>
                                                            <input type="text" name="remarks" placeholder="{{ __('Remarks') }}" value="{{ $entry['attendance']->remarks ?? '' }}" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <button type="submit" class="text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-md px-3 py-1.5">{{ __('Save') }}</button>
                                                        <button type="button" @click="editing = false" class="text-sm text-amber-600 hover:underline">{{ __('Cancel') }}</button>
                                                    </form>
                                                </td>
                                            @else
                                                <td class="px-4 py-3 text-sm">
                                                    @if ($entry['attendance'])
                                                        <x-badge :color="match ($entry['attendance']->status) {
                                                            'present' => 'green',
                                                            'absent' => 'red',
                                                            'late' => 'amber',
                                                            'excused' => 'blue',
                                                            default => 'gray',
                                                        }" class="capitalize">{{ $entry['attendance']->status }}</x-badge>
                                                    @else
                                                        <x-badge color="gray">{{ __('Not yet marked') }}</x-badge>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $entry['attendance']->score ?? '—' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $entry['attendance']->remarks ?? '—' }}</td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center">
                                                <div class="flex flex-col items-center gap-2">
                                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                                    </span>
                                                    <p class="text-sm text-gray-500">{{ __('No actively enrolled students.') }}</p>
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
