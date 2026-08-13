@php($lead = $lead ?? null)

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
    <x-input-label for="course_interested" :value="__('Course Interested In')" />
    <x-text-input id="course_interested" name="course_interested" type="text" class="mt-1 block w-full" :value="old('course_interested', $lead?->course_interested)" />
    <x-input-error class="mt-2" :messages="$errors->get('course_interested')" />
</div>

<div>
    <x-input-label for="source" :value="__('How They Heard About Us')" />
    <x-text-input id="source" name="source" type="text" class="mt-1 block w-full" :value="old('source', $lead?->source)" placeholder="{{ __('e.g. Walk-in, Referral, Social Media') }}" />
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
