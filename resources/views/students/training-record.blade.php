<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Log for :name', ['name' => $student->name]) }}
        </h2>
    </x-slot>

    @php
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $bookIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $clipboardCheckIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $plusIconPath = 'M12 4.5v15m7.5-7.5h-15';
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>
                </span>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Training Log for') }}</p>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ $student->name }}</h3>
                </div>
            </div>

            @if (session('status') === 'training-logged')
                <p class="text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
            @elseif (session('status') === 'attendance-updated')
                <p class="text-sm font-medium text-green-600">{{ __('Training login updated successfully.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-4">
                    <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                    </span>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-amber-100 text-amber-800">{{ __('Student Information') }}</span>
                        <span class="mt-1 block h-0.5 w-8 rounded-full bg-amber-400"></span>
                    </div>
                </div>

                <dl class="divide-y divide-gray-100">
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                            {{ __('Student ID') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2 font-mono font-semibold">{{ $student->student_id_number }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                            {{ __('Name') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->name }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            {{ __('Date of Birth') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ optional($student->date_of_birth)->format('Y-m-d') ?? '—' }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookIconPath }}" /></svg>
                            {{ __('Course') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->courses->pluck('name')->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                            {{ __('Phone') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->phone }}</dd>
                    </div>
                    <div class="py-3 grid grid-cols-3 gap-4 items-center">
                        <dt class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                            {{ __('Email') }}
                        </dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->email }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 border-l-4 border-blue-500 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Training Log History') }}</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-2 py-2">{{ __('S/N') }}</th>
                                <th class="px-2 py-2">{{ __('Date of Training') }}</th>
                                <th class="px-2 py-2">{{ __('Time') }}</th>
                                <th class="px-2 py-2">{{ __('Session') }}</th>
                                <th class="px-2 py-2">{{ __('Type') }}</th>
                                <th class="px-2 py-2">{{ __('Duration') }}</th>
                                <th class="px-2 py-2">{{ __('Instructor') }}</th>
                                <th class="px-2 py-2">{{ __('Vehicle') }}</th>
                                <th class="px-2 py-2">{{ __('Logged By') }}</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($student->attendances as $index => $attendance)
                                <tr>
                                    <td class="px-2 py-2 text-sm">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->date->format('Y-m-d') }}</td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->created_at->format('H:i') }}</td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->sessionPeriod() ?? '—' }}</td>
                                    <td class="px-2 py-2 text-sm">
                                        @if ($attendance->type)
                                            <x-badge color="blue" class="capitalize">{{ $attendance->type }}</x-badge>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->duration ?? '—' }}</td>
                                    <td class="px-2 py-2 text-sm">
                                        @if ($attendance->instructor)
                                            {{ $attendance->instructor->name }}
                                        @else
                                            <x-badge color="gray">{{ __('None') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->vehicle?->name ?? '—' }}</td>
                                    <td class="px-2 py-2 text-sm">{{ $attendance->loggedBy?->name ?? '—' }}</td>
                                    <td class="px-2 py-2 text-sm text-right whitespace-nowrap space-x-2">
                                        @if (auth()->user()->canManageCourses())
                                            <a href="{{ route('attendances.edit', $attendance) }}?redirect_to=training_record" class="text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        @endif
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('attendances.destroy', $attendance) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this training login?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-2 py-3 text-sm text-gray-500">{{ __('No training logins yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if (auth()->user()->canManageCourses())
                @if ($student->courses->isNotEmpty())
                    <div class="relative overflow-hidden rounded-xl bg-blue-50/60 ring-1 ring-blue-100 p-6">
                        <svg class="pointer-events-none absolute -right-4 -bottom-4 h-28 w-28 text-blue-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardCheckIconPath }}" /></svg>

                        <div class="relative flex items-center gap-3 mb-5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-500 text-white">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('Log New Training Session') }}</h3>
                                <p class="text-sm text-gray-500">{{ __("Add details of today's training") }}</p>
                            </div>
                        </div>

                        <x-input-error class="relative mb-4" :messages="$errors->get('student_id')" />

                        <form method="post" action="{{ route('attendances.store') }}" class="relative grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <input type="hidden" name="redirect_to_training_record" value="1">
                            <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                            <input type="hidden" name="status" value="present">

                            <div>
                                <x-input-label for="record_course_id" :value="__('Course')" />
                                <select id="record_course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm" required>
                                    @foreach ($student->courses as $enrolledCourse)
                                        <option value="{{ $enrolledCourse->id }}" @selected($loop->first)>{{ $enrolledCourse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_type" :value="__('Type')" />
                                <select id="record_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm">
                                    @foreach (['practical' => 'Practical', 'classroom' => 'Classroom'] as $value => $label)
                                        <option value="{{ $value }}" @selected($value === 'practical')>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_instructor_id" :value="__('Instructor')" />
                                <select id="record_instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($instructors as $availableInstructor)
                                        <option value="{{ $availableInstructor->id }}">{{ $availableInstructor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_vehicle_id" :value="__('Vehicle')" />
                                <select id="record_vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($vehicles as $availableVehicle)
                                        <option value="{{ $availableVehicle->id }}">{{ $availableVehicle->name }} ({{ $availableVehicle->plate_number }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="record_duration" :value="__('Duration')" />
                                <select id="record_duration" name="duration" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm">
                                    @foreach ([1 => '1 Day (Single Session)', 2 => '2 Days (Double Period / Saturday)', 3 => '3 Days (Sunday)'] as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-3 text-sm font-bold text-black transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                                    {{ __('Save Training Log') }}
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ __('Enroll this student in a course before logging training.') }}</p>
                @endif
            @endif

            <div class="flex items-center gap-4">
                <a href="{{ route('enrolled-trainees.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to Enrolled Trainees') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
