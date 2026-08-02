@php($certificate = $certificate ?? null)

<div>
    <x-input-label for="student_id" :value="__('Student')" />
    <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a student') }}</option>
        @foreach ($students as $availableStudent)
            <option value="{{ $availableStudent->id }}" @selected((int) old('student_id', $certificate?->student_id) === $availableStudent->id)>{{ $availableStudent->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('student_id')" />
</div>

<div>
    <x-input-label for="course_id" :value="__('Course')" />
    <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a course') }}</option>
        @foreach ($courses as $availableCourse)
            <option value="{{ $availableCourse->id }}" @selected((int) old('course_id', $certificate?->course_id) === $availableCourse->id)>{{ $availableCourse->name }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500">{{ __('The selected course must already be marked "completed" for this student on their Enrollments list.') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
</div>

<div>
    <x-input-label for="instructor_id" :value="__('Instructor')" />
    <select id="instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">{{ __('None') }}</option>
        @foreach ($instructors as $availableInstructor)
            <option value="{{ $availableInstructor->id }}" @selected((int) old('instructor_id', $certificate?->instructor_id) === $availableInstructor->id)>{{ $availableInstructor->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('instructor_id')" />
</div>

<div>
    <x-input-label for="issue_date" :value="__('Issue Date')" />
    <x-text-input id="issue_date" name="issue_date" type="date" class="mt-1 block w-full" :value="old('issue_date', optional($certificate?->issue_date)->format('Y-m-d') ?? now()->toDateString())" required />
    <x-input-error class="mt-2" :messages="$errors->get('issue_date')" />
</div>
