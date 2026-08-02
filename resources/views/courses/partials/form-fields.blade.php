@php($course = $course ?? null)
@php($selectedInstructors = old('instructors', $course?->instructors->pluck('id')->all() ?? []))

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $course?->name)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $course?->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>

<div>
    <x-input-label for="course_type" :value="__('Course Type')" />
    <select id="course_type" name="course_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Both'] as $value => $label)
            <option value="{{ $value }}" @selected(old('course_type', $course?->course_type) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_type')" />
</div>

<div>
    <x-input-label for="duration_hours" :value="__('Duration (hours)')" />
    <x-text-input id="duration_hours" name="duration_hours" type="number" min="1" class="mt-1 block w-full" :value="old('duration_hours', $course?->duration_hours)" required />
    <x-input-error class="mt-2" :messages="$errors->get('duration_hours')" />
</div>

<div>
    <x-input-label for="fee" :value="__('Fee')" />
    <x-text-input id="fee" name="fee" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('fee', $course?->fee)" required />
    <x-input-error class="mt-2" :messages="$errors->get('fee')" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $course?->status ?? 'active') === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>

<div>
    <x-input-label :value="__('Instructors')" />
    <div class="mt-1 space-y-1 border border-gray-200 rounded-md p-3 max-h-48 overflow-y-auto">
        @forelse ($instructors as $availableInstructor)
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="instructors[]" value="{{ $availableInstructor->id }}" @checked(in_array($availableInstructor->id, $selectedInstructors)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                {{ $availableInstructor->name }}
            </label>
        @empty
            <p class="text-sm text-gray-500">{{ __('No instructors available yet.') }}</p>
        @endforelse
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('instructors')" />
</div>
