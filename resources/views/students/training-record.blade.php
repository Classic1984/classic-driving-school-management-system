<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Record') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                @if (session('status') === 'training-logged')
                    <p class="text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                @endif
                <x-input-error :messages="$errors->get('student_id')" />

                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student ID') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 font-mono">{{ $student->student_id_number }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ optional($student->date_of_birth)->format('Y-m-d') ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->courses->pluck('name')->implode(', ') ?: '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->phone }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $student->email }}</dd>
                    </div>
                </dl>

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Training Login History') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Course') }}</th>
                                    <th class="px-2 py-1">{{ __('Session') }}</th>
                                    <th class="px-2 py-1">{{ __('Instructor') }}</th>
                                    <th class="px-2 py-1">{{ __('Vehicle') }}</th>
                                    <th class="px-2 py-1">{{ __('Logged By') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($student->attendances as $attendance)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->date->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->course->name }}</td>
                                        <td class="px-2 py-1 text-sm capitalize">{{ $attendance->session ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->instructor?->name ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->vehicle ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">{{ $attendance->loggedBy?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-2 py-2 text-sm text-gray-500">{{ __('No training logins yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($student->courses->isNotEmpty())
                        <form method="post" action="{{ route('attendances.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-5 gap-4 items-end">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <input type="hidden" name="redirect_to_training_record" value="1">
                            <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                            <input type="hidden" name="status" value="present">

                            <div>
                                <x-input-label for="record_course_id" :value="__('Course')" />
                                <select id="record_course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                    <option value="">{{ __('Select a course') }}</option>
                                    @foreach ($student->courses as $enrolledCourse)
                                        <option value="{{ $enrolledCourse->id }}">{{ $enrolledCourse->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_session" :value="__('Session')" />
                                <select id="record_session" name="session" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                    <option value="">{{ __('Not specified') }}</option>
                                    @foreach (['morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening'] as $value => $label)
                                        <option value="{{ $value }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_instructor_id" :value="__('Instructor')" />
                                <select id="record_instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($instructors as $availableInstructor)
                                        <option value="{{ $availableInstructor->id }}">{{ $availableInstructor->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label for="record_vehicle" :value="__('Vehicle')" />
                                <x-text-input id="record_vehicle" name="vehicle" type="text" class="mt-1 block w-full" />
                            </div>

                            <div>
                                <x-primary-button type="submit">{{ __('Log Training') }}</x-primary-button>
                            </div>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-gray-500">{{ __('Enroll this student in a course before logging training.') }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <a href="{{ route('enrolled-trainees.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to Enrolled Trainees') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
