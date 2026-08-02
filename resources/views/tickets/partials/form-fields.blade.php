@php($ticket = $ticket ?? null)

<div>
    <x-input-label for="student_id" :value="__('Student')" />
    <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a student') }}</option>
        @foreach ($students as $availableStudent)
            <option value="{{ $availableStudent->id }}" @selected((int) old('student_id', $ticket?->student_id) === $availableStudent->id)>{{ $availableStudent->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('student_id')" />
</div>

<div>
    <x-input-label for="course_id" :value="__('Course')" />
    <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a course') }}</option>
        @foreach ($courses as $availableCourse)
            <option value="{{ $availableCourse->id }}" @selected((int) old('course_id', $ticket?->course_id) === $availableCourse->id)>{{ $availableCourse->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
</div>

<div>
    <x-input-label for="instructor_id" :value="__('Instructor')" />
    <select id="instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">{{ __('None') }}</option>
        @foreach ($instructors as $availableInstructor)
            <option value="{{ $availableInstructor->id }}" @selected((int) old('instructor_id', $ticket?->instructor_id) === $availableInstructor->id)>{{ $availableInstructor->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('instructor_id')" />
</div>

<div>
    <x-input-label for="date" :value="__('Training Date')" />
    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', optional($ticket?->date)->format('Y-m-d') ?? now()->toDateString())" required />
    <x-input-error class="mt-2" :messages="$errors->get('date')" />
</div>

<div>
    <x-input-label for="vehicle" :value="__('Vehicle')" />
    <x-text-input id="vehicle" name="vehicle" type="text" class="mt-1 block w-full" :value="old('vehicle', $ticket?->vehicle)" />
    <x-input-error class="mt-2" :messages="$errors->get('vehicle')" />
</div>

<div>
    <x-input-label for="lesson_number" :value="__('Lesson Number')" />
    <x-text-input id="lesson_number" name="lesson_number" type="number" min="1" class="mt-1 block w-full" :value="old('lesson_number', $ticket?->lesson_number)" />
    <x-input-error class="mt-2" :messages="$errors->get('lesson_number')" />
</div>
