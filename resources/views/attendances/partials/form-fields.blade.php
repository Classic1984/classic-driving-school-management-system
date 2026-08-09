@php($attendance = $attendance ?? null)

<div>
    <x-input-label for="student_id" :value="__('Student')" />
    <select id="student_id" name="student_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a student') }}</option>
        @foreach ($students as $availableStudent)
            <option value="{{ $availableStudent->id }}" @selected((int) old('student_id', $attendance?->student_id) === $availableStudent->id)>{{ $availableStudent->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('student_id')" />
</div>

<div>
    <x-input-label for="course_id" :value="__('Course')" />
    <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a course') }}</option>
        @foreach ($courses as $availableCourse)
            <option value="{{ $availableCourse->id }}" @selected((int) old('course_id', $attendance?->course_id) === $availableCourse->id)>{{ $availableCourse->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_id')" />
</div>

<div>
    <x-input-label for="instructor_id" :value="__('Instructor')" />
    <select id="instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('None') }}</option>
        @foreach ($instructors as $availableInstructor)
            <option value="{{ $availableInstructor->id }}" @selected((int) old('instructor_id', $attendance?->instructor_id) === $availableInstructor->id)>{{ $availableInstructor->name }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('instructor_id')" />
</div>

<div>
    <x-input-label for="date" :value="__('Date')" />
    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', optional($attendance?->date)->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
    <x-input-error class="mt-2" :messages="$errors->get('date')" />
</div>

<div>
    <x-input-label for="type" :value="__('Type')" />
    <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Not specified') }}</option>
        @foreach (['practical' => 'Practical', 'classroom' => 'Classroom'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $attendance?->type) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('type')" />
</div>

<div>
    <x-input-label for="duration" :value="__('Duration')" />
    <select id="duration" name="duration" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        @foreach ([1 => '1 Day (Weekday)', 2 => '2 Days (Saturday)', 3 => '3 Days (Sunday)'] as $value => $label)
            <option value="{{ $value }}" @selected((int) old('duration', $attendance?->duration ?? 1) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('duration')" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $attendance?->status ?? 'present') === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>

<div>
    <x-input-label for="notes" :value="__('Notes')" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes', $attendance?->notes) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
</div>
