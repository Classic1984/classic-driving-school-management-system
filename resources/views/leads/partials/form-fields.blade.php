@php($lead = $lead ?? null)
@php($courses = $courses ?? collect())
@php($selectedCourse = old('course_interested', $lead?->course_interested))
@php($courseOptions = $selectedCourse && ! $courses->contains($selectedCourse) ? $courses->push($selectedCourse) : $courses)
@php($selectedSource = old('source', $lead?->source))
@php($sourceOptions = $selectedSource && ! in_array($selectedSource, \App\Models\Lead::SOURCES, true) ? [...\App\Models\Lead::SOURCES, $selectedSource] : \App\Models\Lead::SOURCES)

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $lead?->name)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $lead?->phone)" required />
    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
</div>

<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $lead?->email)" />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div>
    <x-input-label for="course_interested" :value="__('Course Interested In')" />
    <select id="course_interested" name="course_interested" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select a course') }}</option>
        @foreach ($courseOptions as $course)
            <option value="{{ $course }}" @selected($selectedCourse === $course)>{{ $course }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_interested')" />
</div>

<div>
    <x-input-label for="source" :value="__('How They Heard About Us')" />
    <select id="source" name="source" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select a source') }}</option>
        @foreach ($sourceOptions as $source)
            <option value="{{ $source }}" @selected($selectedSource === $source)>{{ $source }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('source')" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (\App\Models\Lead::STATUSES as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $lead?->status ?? 'new') === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>

<div>
    <x-input-label for="notes" :value="__('Notes')" />
    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('notes', $lead?->notes) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
</div>
