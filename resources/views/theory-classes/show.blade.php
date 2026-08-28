<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Class') }} — {{ $theoryClass->class_date->format('l, M j, Y') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'theory-class-updated')
                <p class="text-sm font-medium text-green-600">{{ __('Class details updated.') }}</p>
            @elseif (session('status') === 'theory-attendance-saved')
                <p class="text-sm font-medium text-green-600">{{ __('Attendance saved.') }}</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $roster->count() }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Expected') }}</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $theoryClass->presentCount() }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Present') }}</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $theoryClass->absentCount() }}</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Absent') }}</div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-bold text-gray-800">{{ $theoryClass->attendancePercentage() }}%</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Attendance') }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Class Details') }}</h3>

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
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">{{ __('Topic') }}</dt>
                            <dd class="font-medium">{{ $theoryClass->topic ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Instructor') }}</dt>
                            <dd class="font-medium">{{ $theoryClass->instructor?->name ?? '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">{{ __('Notes') }}</dt>
                            <dd class="font-medium">{{ $theoryClass->notes ?: '—' }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">{{ __('Lecture Material') }}</dt>
                            <dd class="font-medium">
                                @if ($theoryClass->materials_path)
                                    <a href="{{ $theoryClass->materialsUrl() }}" target="_blank" class="text-amber-600 hover:underline">{{ $theoryClass->materials_original_name ?: __('View') }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                    </dl>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Roster') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2">{{ __('Score') }}</th>
                                <th class="px-4 py-2">{{ __('Remarks') }}</th>
                                @if (auth()->user()->canManageCourses())
                                    <th class="px-4 py-2"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($roster as $entry)
                                <tr x-data="{ editing: false }">
                                    <td class="px-4 py-2 font-medium">{{ $entry['student']->name }}</td>

                                    @if (auth()->user()->canManageCourses())
                                        <td class="px-4 py-2" colspan="3" x-show="!editing">
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
                                        <td class="px-4 py-2 text-right" x-show="!editing">
                                            <button type="button" @click="editing = true" class="text-sm text-amber-600 hover:underline">
                                                {{ $entry['attendance'] ? __('Edit') : __('Mark') }}
                                            </button>
                                        </td>

                                        <td class="px-4 py-2" colspan="4" x-show="editing">
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
                                                <button type="button" @click="editing = false" class="text-sm text-gray-500 hover:underline">{{ __('Cancel') }}</button>
                                            </form>
                                        </td>
                                    @else
                                        <td class="px-4 py-2">
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
                                        <td class="px-4 py-2">{{ $entry['attendance']->score ?? '—' }}</td>
                                        <td class="px-4 py-2">{{ $entry['attendance']->remarks ?? '—' }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No actively enrolled students.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
