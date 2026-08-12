@php($student = $student ?? null)
@php($fieldsLocked = $student && ! auth()->user()->isDirector())

@if ($fieldsLocked)
    <div class="rounded-md bg-gray-50 border border-gray-200 p-4 space-y-3">
        <p class="text-xs font-medium text-gray-500">{{ __('🔒 Director-controlled information') }}</p>

        <div>
            <x-input-label :value="__('Name')" />
            <p class="mt-1 text-sm text-gray-900">{{ $student->name }}</p>
        </div>
        <div>
            <x-input-label :value="__('Phone')" />
            <p class="mt-1 text-sm text-gray-900">{{ $student->phone }}</p>
        </div>
        <div>
            <x-input-label :value="__('Date of Birth')" />
            <p class="mt-1 text-sm text-gray-900">{{ optional($student->date_of_birth)->format('Y-m-d') ?? '—' }}</p>
        </div>

        <a href="{{ route('student-correction-requests.create', $student) }}" class="text-sm text-amber-600 hover:underline">{{ __('Request a Correction') }}</a>
    </div>
    <input type="hidden" name="name" value="{{ $student->name }}">
    <input type="hidden" name="phone" value="{{ $student->phone }}">
    <input type="hidden" name="date_of_birth" value="{{ optional($student->date_of_birth)->format('Y-m-d') }}">
@else
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $student?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="phone" :value="__('Phone')" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $student?->phone)" required />
        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
        <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($student?->date_of_birth)->format('Y-m-d'))" required />
        <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
    </div>
@endif

<div>
    <x-input-label for="email" :value="__('Email')" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $student?->email)" required />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div>
    <x-input-label for="address" :value="__('Address')" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $student?->address)" />
    <x-input-error class="mt-2" :messages="$errors->get('address')" />
</div>

<div>
    <x-input-label for="mother_maiden_name" :value="__('Mother Maiden Name')" />
    <x-text-input id="mother_maiden_name" name="mother_maiden_name" type="text" class="mt-1 block w-full" :value="old('mother_maiden_name', $student?->mother_maiden_name)" />
    <x-input-error class="mt-2" :messages="$errors->get('mother_maiden_name')" />
</div>

<div>
    <x-input-label for="sex" :value="__('Sex')" />
    <select id="sex" name="sex" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
            <option value="{{ $value }}" @selected(old('sex', $student?->sex) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('sex')" />
</div>

@php($statesAndLgas = config('nigeria.states'))
<div>
    <x-input-label for="state_of_origin" :value="__('State of Origin')" />
    <select id="state_of_origin" name="state_of_origin" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        @foreach (array_keys($statesAndLgas) as $state)
            <option value="{{ $state }}" @selected(old('state_of_origin', $student?->state_of_origin) === $state)>{{ $state }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('state_of_origin')" />
</div>

<div>
    <x-input-label for="local_government_area" :value="__('Local Govt. Area')" />
    <select id="local_government_area" name="local_government_area" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select a state first') }}</option>
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('local_government_area')" />
</div>

<script>
    (function () {
        var statesAndLgas = @json($statesAndLgas);
        var selectedLga = @json(old('local_government_area', $student?->local_government_area));
        var stateSelect = document.getElementById('state_of_origin');
        var lgaSelect = document.getElementById('local_government_area');

        function populateLgas(stateName, preselect) {
            var lgas = statesAndLgas[stateName] || [];
            lgaSelect.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = lgas.length ? 'Select' : 'Select a state first';
            lgaSelect.appendChild(placeholder);

            lgas.forEach(function (lga) {
                var option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                if (lga === preselect) {
                    option.selected = true;
                }
                lgaSelect.appendChild(option);
            });
        }

        if (stateSelect.value) {
            populateLgas(stateSelect.value, selectedLga);
        }

        stateSelect.addEventListener('change', function () {
            populateLgas(stateSelect.value, null);
        });
    })();
</script>

<div>
    <x-input-label for="occupation" :value="__('Occupation')" />
    <select id="occupation" name="occupation" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        @foreach (['student' => 'Student', 'business' => 'Business', 'other' => 'Others'] as $value => $label)
            <option value="{{ $value }}" @selected(old('occupation', $student?->occupation) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('occupation')" />
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
    <x-input-label for="vehicle_class" :value="__('Class of Vehicle You Wish to Operate After Training')" />
    <select id="vehicle_class" name="vehicle_class" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        @foreach (['light' => 'Light', 'heavy' => 'Heavy'] as $value => $label)
            <option value="{{ $value }}" @selected(old('vehicle_class', $student?->vehicle_class) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('vehicle_class')" />
</div>

@php($hasDrivingExperience = old('has_driving_experience', $student?->has_driving_experience))
<div>
    <x-input-label for="has_driving_experience" :value="__('Do You Have Any Previous Knowledge of Driving?')" />
    <select id="has_driving_experience" name="has_driving_experience" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        <option value="1" @selected($hasDrivingExperience !== null && $hasDrivingExperience !== '' && filter_var($hasDrivingExperience, FILTER_VALIDATE_BOOLEAN))>{{ __('Yes') }}</option>
        <option value="0" @selected($hasDrivingExperience !== null && $hasDrivingExperience !== '' && ! filter_var($hasDrivingExperience, FILTER_VALIDATE_BOOLEAN))>{{ __('No') }}</option>
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('has_driving_experience')" />
</div>

@php($wearsGlasses = old('wears_glasses', $student?->wears_glasses))
<div>
    <x-input-label for="wears_glasses" :value="__('Do You Wear Glasses to Drive?')" />
    <select id="wears_glasses" name="wears_glasses" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        <option value="1" @selected($wearsGlasses !== null && $wearsGlasses !== '' && filter_var($wearsGlasses, FILTER_VALIDATE_BOOLEAN))>{{ __('Yes') }}</option>
        <option value="0" @selected($wearsGlasses !== null && $wearsGlasses !== '' && ! filter_var($wearsGlasses, FILTER_VALIDATE_BOOLEAN))>{{ __('No') }}</option>
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('wears_glasses')" />
</div>

<div>
    <x-input-label for="referral_source" :value="__('How Did You Know About Us?')" />
    <select id="referral_source" name="referral_source" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <option value="">{{ __('Select') }}</option>
        @foreach (['flyer' => 'Flyer', 'referral' => 'Referral', 'facebook' => 'Facebook', 'other' => 'Others'] as $value => $label)
            <option value="{{ $value }}" @selected(old('referral_source', $student?->referral_source) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('referral_source')" />
</div>

<div>
    <x-input-label for="referral_source_other" :value="__('If Others, Please Specify')" />
    <x-text-input id="referral_source_other" name="referral_source_other" type="text" class="mt-1 block w-full" :value="old('referral_source_other', $student?->referral_source_other)" />
    <x-input-error class="mt-2" :messages="$errors->get('referral_source_other')" />
</div>

<fieldset class="border border-gray-200 rounded-md p-4">
    <legend class="text-sm font-medium text-gray-700 px-1">{{ __('Next of Kin') }}</legend>

    <div class="space-y-6">
        <div>
            <x-input-label for="next_of_kin_name" :value="__('Name')" />
            <x-text-input id="next_of_kin_name" name="next_of_kin_name" type="text" class="mt-1 block w-full" :value="old('next_of_kin_name', $student?->next_of_kin_name)" />
            <x-input-error class="mt-2" :messages="$errors->get('next_of_kin_name')" />
        </div>

        <div>
            <x-input-label for="next_of_kin_address" :value="__('Address')" />
            <x-text-input id="next_of_kin_address" name="next_of_kin_address" type="text" class="mt-1 block w-full" :value="old('next_of_kin_address', $student?->next_of_kin_address)" />
            <x-input-error class="mt-2" :messages="$errors->get('next_of_kin_address')" />
        </div>

        <div>
            <x-input-label for="next_of_kin_phone" :value="__('Phone No.')" />
            <x-text-input id="next_of_kin_phone" name="next_of_kin_phone" type="text" class="mt-1 block w-full" :value="old('next_of_kin_phone', $student?->next_of_kin_phone)" />
            <x-input-error class="mt-2" :messages="$errors->get('next_of_kin_phone')" />
        </div>

        <div>
            <x-input-label for="next_of_kin_email" :value="__('Email')" />
            <x-text-input id="next_of_kin_email" name="next_of_kin_email" type="email" class="mt-1 block w-full" :value="old('next_of_kin_email', $student?->next_of_kin_email)" />
            <x-input-error class="mt-2" :messages="$errors->get('next_of_kin_email')" />
        </div>
    </div>
</fieldset>

<div>
    <x-input-label for="photo" :value="__('3 Colour Passport Size Photograph')" />
    @if ($student?->photo_path)
        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ __('Current photo') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-md border border-gray-200">
    @endif
    <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
</div>

@if (! $student && ! auth()->user()->isDirector())
    <div class="rounded-md bg-gray-50 border border-gray-200 p-4">
        <p class="text-sm text-gray-600">{{ __('🔒 Assigning a training program is Director-only. This student will be registered without one - a Director can enroll them afterward.') }}</p>
    </div>
@endif

@if (! $student && auth()->user()->isDirector())
    @include('students.partials.enrollment-fields', ['courses' => $courses])
@endif

<div>
    <x-input-label for="enrollment_date" :value="__('Enrollment Date')" />
    @if ($student)
        <x-text-input id="enrollment_date" name="enrollment_date" type="date" class="mt-1 block w-full" :value="old('enrollment_date', optional($student->enrollment_date)->format('Y-m-d'))" :max="now()->format('Y-m-d')" required />
    @else
        <x-text-input id="enrollment_date" type="text" class="mt-1 block w-full bg-gray-100" :value="now()->format('Y-m-d')" disabled />
        <input type="hidden" name="enrollment_date" value="{{ now()->toDateString() }}">
        <p class="mt-1 text-xs text-gray-500">{{ __('Registration always enrolls as of today.') }}</p>
    @endif
    <x-input-error class="mt-2" :messages="$errors->get('enrollment_date')" />
</div>

@if ($student)
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
            @foreach (['active' => 'Active', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $student->status) === $value)>{{ __($label) }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">{{ __('Active/Completed are normally set automatically as training progresses. Use Withdrawn to record that a student has left the program.') }}</p>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />
    </div>
@endif
