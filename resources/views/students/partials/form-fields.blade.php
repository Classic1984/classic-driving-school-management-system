@php($student = $student ?? null)

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $student?->name)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $student?->email)" required />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $student?->phone)" required />
    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
</div>

<div>
    <x-input-label for="address" :value="__('Address')" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $student?->address)" />
    <x-input-error class="mt-2" :messages="$errors->get('address')" />
</div>

<div>
    <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($student?->date_of_birth)->format('Y-m-d'))" required />
    <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
</div>

<div>
    <x-input-label for="license_number" :value="__('License Number')" />
    <x-text-input id="license_number" name="license_number" type="text" class="mt-1 block w-full" :value="old('license_number', $student?->license_number)" />
    <x-input-error class="mt-2" :messages="$errors->get('license_number')" />
</div>

<div>
    <x-input-label for="course_type" :value="__('Course Type')" />
    <select id="course_type" name="course_type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Both'] as $value => $label)
            <option value="{{ $value }}" @selected(old('course_type', $student?->course_type) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('course_type')" />
</div>

<div>
    <x-input-label for="enrollment_date" :value="__('Enrollment Date')" />
    <x-text-input id="enrollment_date" name="enrollment_date" type="date" class="mt-1 block w-full" :value="old('enrollment_date', optional($student?->enrollment_date)->format('Y-m-d'))" required />
    <x-input-error class="mt-2" :messages="$errors->get('enrollment_date')" />
</div>

<div>
    <x-input-label for="status" :value="__('Status')" />
    <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        @foreach (['active' => 'Active', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $student?->status ?? 'active') === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('status')" />
</div>
