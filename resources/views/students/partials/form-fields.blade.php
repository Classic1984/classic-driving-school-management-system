@php
    $student = $student ?? null;
    $fieldsLocked = $student && ! auth()->user()->isDirector();

    $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
    $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
    $infoCircleIconPath = 'M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
    $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
    $documentIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
    $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';

    $sectionHeading = function (string $iconPath, string $label) {
        return '<div class="flex items-center gap-3 mb-4">'
            .'<span class="inline-flex items-center gap-1.5 rounded-full bg-gold-100 px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-gold-800">'
            .'<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="'.$iconPath.'" /></svg>'
            .e(__($label))
            .'</span>'
            .'<span class="h-px flex-1 bg-gray-100"></span>'
            .'</div>';
    };
@endphp

@if ($fieldsLocked)
    @php($sexLabels = ['male' => 'Male', 'female' => 'Female'])
    @php($occupationLabels = ['student' => 'Student', 'business' => 'Business', 'other' => 'Others'])
    @php($courseTypeLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Both'])
    @php($vehicleClassLabels = ['light' => 'Light', 'heavy' => 'Heavy'])
    @php($referralSourceLabels = ['flyer' => 'Flyer', 'referral' => 'Referral', 'facebook' => 'Facebook', 'other' => 'Others'])
    @php($statusLabels = ['active' => 'Active', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'])
    @php($yesNo = fn ($value) => $value === null ? '—' : ($value ? __('Yes') : __('No')))

    <div class="rounded-xl bg-gold-50 ring-1 ring-gold-200 p-4 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold-500/20 text-gold-700">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            </span>
            <div>
                <p class="text-sm font-bold text-gold-800">{{ __('Director-controlled information') }}</p>
                <p class="text-sm text-gold-700 mt-0.5">{{ __('Only a Director can edit an already registered student. Submit a request below and a Director will review it.') }}</p>
            </div>
        </div>
        <a href="{{ route('student-correction-requests.create', $student) }}" class="shrink-0 inline-flex items-center gap-2 rounded-lg bg-gold-500 hover:bg-gold-600 px-4 py-2 text-sm font-bold text-navy transition">{{ __('Request a Correction') }}</a>
    </div>

    {{-- These fields are all required by the validator, so a hidden input
    carries the unchanged value forward - the server strips it from the
    update regardless, but omitting it here would fail validation before
    the request even reaches that check. --}}
    <input type="hidden" name="name" value="{{ $student->name }}">
    <input type="hidden" name="phone" value="{{ $student->phone }}">
    <input type="hidden" name="date_of_birth" value="{{ optional($student->date_of_birth)->format('Y-m-d') }}">
    <input type="hidden" name="email" value="{{ $student->email }}">
    <input type="hidden" name="enrollment_date" value="{{ optional($student->enrollment_date)->format('Y-m-d') }}">
    <input type="hidden" name="status" value="{{ $student->status }}">
@endif

<div>
    {!! $sectionHeading($personIconPath, 'Personal Information') !!}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($fieldsLocked)
            <div>
                <x-input-label :value="__('Name')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->name }}</p>
            </div>
            <div>
                <x-input-label :value="__('Phone')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->phone }}</p>
            </div>
            <div>
                <x-input-label :value="__('Date of Birth')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ optional($student->date_of_birth)->format('l, M j, Y') ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Sex')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $sexLabels[$student->sex] ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Mother Maiden Name')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->mother_maiden_name ?? '—' }}</p>
            </div>
        @else
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $student?->name ?? request('name'))" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $student?->phone ?? request('phone'))" required />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
                <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($student?->date_of_birth)->format('Y-m-d'))" required />
                <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
            </div>

            <div>
                <x-input-label for="sex" :value="__('Sex')" />
                <x-select id="sex" name="sex" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['male' => 'Male', 'female' => 'Female'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('sex', $student?->sex) === $value)>{{ __($label) }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('sex')" />
            </div>

            <div>
                <x-input-label for="mother_maiden_name" :value="__('Mother Maiden Name')" />
                <x-text-input id="mother_maiden_name" name="mother_maiden_name" type="text" class="mt-1 block w-full" :value="old('mother_maiden_name', $student?->mother_maiden_name)" />
                <x-input-error class="mt-2" :messages="$errors->get('mother_maiden_name')" />
            </div>
        @endif
    </div>
</div>

<div>
    {!! $sectionHeading($envelopeIconPath, 'Contact Information') !!}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($fieldsLocked)
            <div>
                <x-input-label :value="__('Email')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->email }}</p>
            </div>
            <div>
                <x-input-label :value="__('Address')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->address ?? '—' }}</p>
            </div>
        @else
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
        @endif
    </div>
</div>

<div>
    {!! $sectionHeading($infoCircleIconPath, 'Additional Information') !!}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($fieldsLocked)
            <div>
                <x-input-label :value="__('State of Origin')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->state_of_origin ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Local Govt. Area')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->local_government_area ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Occupation')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $occupationLabels[$student->occupation] ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Course Type')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $courseTypeLabels[$student->course_type] ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Class of Vehicle You Wish to Operate After Training')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $vehicleClassLabels[$student->vehicle_class] ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('License Number')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->license_number ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Do You Have Any Previous Knowledge of Driving?')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $yesNo($student->has_driving_experience) }}</p>
            </div>
            <div>
                <x-input-label :value="__('Do You Wear Glasses to Drive?')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $yesNo($student->wears_glasses) }}</p>
            </div>
            <div>
                <x-input-label :value="__('How Did You Know About Us?')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $referralSourceLabels[$student->referral_source] ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('If Others, Please Specify')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->referral_source_other ?? '—' }}</p>
            </div>
        @else
            @php($statesAndLgas = config('nigeria.states'))
            <div>
                <x-input-label for="state_of_origin" :value="__('State of Origin')" />
                <x-select id="state_of_origin" name="state_of_origin" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (array_keys($statesAndLgas) as $state)
                        <option value="{{ $state }}" @selected(old('state_of_origin', $student?->state_of_origin) === $state)>{{ $state }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('state_of_origin')" />
            </div>

            <div>
                <x-input-label for="local_government_area" :value="__('Local Govt. Area')" />
                <x-select id="local_government_area" name="local_government_area" class="mt-1 block w-full">
                    <option value="">{{ __('Select a state first') }}</option>
                </x-select>
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
                <x-select id="occupation" name="occupation" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['student' => 'Student', 'business' => 'Business', 'other' => 'Others'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('occupation', $student?->occupation) === $value)>{{ __($label) }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('occupation')" />
            </div>

            <div @if (! $student) x-show="registrationType === 'course'" @endif>
                <x-input-label for="course_type" :value="__('Course Type')" />
                <x-select id="course_type" name="course_type" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Both'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('course_type', $student?->course_type) === $value)>{{ __($label) }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('course_type')" />
            </div>

            <div @if (! $student) x-show="registrationType === 'course'" @endif>
                <x-input-label for="vehicle_class" :value="__('Class of Vehicle You Wish to Operate After Training')" />
                <x-select id="vehicle_class" name="vehicle_class" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['light' => 'Light', 'heavy' => 'Heavy'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('vehicle_class', $student?->vehicle_class) === $value)>{{ __($label) }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('vehicle_class')" />
            </div>

            <div>
                <x-input-label for="license_number" :value="__('License Number')" />
                <x-text-input id="license_number" name="license_number" type="text" class="mt-1 block w-full" :value="old('license_number', $student?->license_number ?? request('license_number'))" />
                <x-input-error class="mt-2" :messages="$errors->get('license_number')" />
            </div>

            @php($hasDrivingExperience = old('has_driving_experience', $student?->has_driving_experience))
            <div>
                <x-input-label for="has_driving_experience" :value="__('Do You Have Any Previous Knowledge of Driving?')" />
                <x-select id="has_driving_experience" name="has_driving_experience" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    <option value="1" @selected($hasDrivingExperience !== null && $hasDrivingExperience !== '' && filter_var($hasDrivingExperience, FILTER_VALIDATE_BOOLEAN))>{{ __('Yes') }}</option>
                    <option value="0" @selected($hasDrivingExperience !== null && $hasDrivingExperience !== '' && ! filter_var($hasDrivingExperience, FILTER_VALIDATE_BOOLEAN))>{{ __('No') }}</option>
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('has_driving_experience')" />
            </div>

            @php($wearsGlasses = old('wears_glasses', $student?->wears_glasses))
            <div>
                <x-input-label for="wears_glasses" :value="__('Do You Wear Glasses to Drive?')" />
                <x-select id="wears_glasses" name="wears_glasses" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    <option value="1" @selected($wearsGlasses !== null && $wearsGlasses !== '' && filter_var($wearsGlasses, FILTER_VALIDATE_BOOLEAN))>{{ __('Yes') }}</option>
                    <option value="0" @selected($wearsGlasses !== null && $wearsGlasses !== '' && ! filter_var($wearsGlasses, FILTER_VALIDATE_BOOLEAN))>{{ __('No') }}</option>
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('wears_glasses')" />
            </div>

            <div>
                <x-input-label for="referral_source" :value="__('How Did You Know About Us?')" />
                <x-select id="referral_source" name="referral_source" class="mt-1 block w-full">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['flyer' => 'Flyer', 'referral' => 'Referral', 'facebook' => 'Facebook', 'other' => 'Others'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('referral_source', $student?->referral_source) === $value)>{{ __($label) }}</option>
                    @endforeach
                </x-select>
                <x-input-error class="mt-2" :messages="$errors->get('referral_source')" />
            </div>

            <div>
                <x-input-label for="referral_source_other" :value="__('If Others, Please Specify')" />
                <x-text-input id="referral_source_other" name="referral_source_other" type="text" class="mt-1 block w-full" :value="old('referral_source_other', $student?->referral_source_other)" />
                <x-input-error class="mt-2" :messages="$errors->get('referral_source_other')" />
            </div>
        @endif
    </div>
</div>

@if (! $student)
    @php($registrationType = (! old('course_id') && ! empty(old('service_ids'))) ? 'service' : 'course')
    <div>
        <x-input-label :value="__('Registration Type')" />
        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label
                class="relative flex items-start gap-3 rounded-xl border p-4 cursor-pointer transition"
                :class="registrationType === 'course' ? 'border-gold-500 bg-gold-50 ring-1 ring-gold-500' : 'border-line hover:bg-paper'"
            >
                <input type="radio" name="registration_type" value="course" @checked($registrationType === 'course') @change="setRegistrationType('course')" class="mt-0.5 border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500">
                <span class="text-sm text-gray-700">{{ __('Enroll in a Course') }}</span>
            </label>
            <label
                class="relative flex items-start gap-3 rounded-xl border p-4 cursor-pointer transition"
                :class="registrationType === 'service' ? 'border-gold-500 bg-gold-50 ring-1 ring-gold-500' : 'border-line hover:bg-paper'"
            >
                <input type="radio" name="registration_type" value="service" @checked($registrationType === 'service') @change="setRegistrationType('service')" class="mt-0.5 border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500">
                <span class="text-sm text-gray-700">{{ __("Register for a Service Only (Learner's Permit, Driver's Licence, Certificate) - no course") }}</span>
            </label>
        </div>
    </div>
@endif

<fieldset class="rounded-xl ring-1 ring-line bg-paper/40 p-4">
    <legend class="inline-flex items-center gap-1.5 rounded-full bg-gold-100 px-3 py-1.5 text-xs font-bold uppercase tracking-widest text-gold-800">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
        {{ __('Next of Kin') }}
    </legend>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
        @if ($fieldsLocked)
            <div>
                <x-input-label :value="__('Name')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->next_of_kin_name ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Address')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->next_of_kin_address ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Phone No.')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->next_of_kin_phone ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Email')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $student->next_of_kin_email ?? '—' }}</p>
            </div>
        @else
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
        @endif
    </div>
</fieldset>

<div>
    {!! $sectionHeading($documentIconPath, 'Documents') !!}

    <div class="space-y-6">
        <div
            x-data="{
                webcamOpen: false,
                stream: null,
                previewUrl: null,
                error: '',
                async openWebcam() {
                    this.error = '';

                    if (! navigator.mediaDevices?.getUserMedia) {
                        this.error = '{{ __('Webcam capture is not supported in this browser.') }}';
                        return;
                    }

                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                        this.webcamOpen = true;
                        this.$nextTick(() => { this.$refs.video.srcObject = this.stream; });
                    } catch (e) {
                        this.error = '{{ __('Could not access the camera - check your browser permissions.') }}';
                    }
                },
                closeWebcam() {
                    this.stream?.getTracks().forEach(track => track.stop());
                    this.stream = null;
                    this.webcamOpen = false;
                },
                capture() {
                    const video = this.$refs.video;
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);

                    canvas.toBlob((blob) => {
                        if (! blob) return;

                        const file = new File([blob], 'webcam-photo.jpg', { type: 'image/jpeg' });
                        const transfer = new DataTransfer();
                        transfer.items.add(file);
                        this.$refs.photoInput.files = transfer.files;

                        if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                        this.previewUrl = URL.createObjectURL(blob);

                        this.closeWebcam();
                    }, 'image/jpeg', 0.9);
                },
            }"
            @beforeunload.window="closeWebcam()"
            class="rounded-xl border-2 border-dashed border-line bg-paper/40 p-4"
        >
            <x-input-label for="photo" :value="__('3 Colour Passport Size Photograph')" />
            @if ($student?->photo_path)
                <img src="{{ Storage::disk('public')->url($student->photo_path) }}" alt="{{ __('Current photo') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-lg ring-1 ring-line">
            @endif
            <template x-if="previewUrl">
                <img :src="previewUrl" alt="{{ __('Captured photo preview') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-lg ring-2 ring-gold-400">
            </template>

            <div class="mt-1 flex items-center gap-3">
                <input id="photo" name="photo" x-ref="photoInput" type="file" accept="image/*" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gold-800 hover:file:bg-gold-100">
                <button type="button" @click="openWebcam" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-2 ring-1 ring-line rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-paper transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                    {{ __('Use Webcam') }}
                </button>
            </div>

            <p x-show="error" x-text="error" class="mt-1 text-sm text-red-600"></p>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />

            <div x-show="webcamOpen" class="mt-3 rounded-lg ring-1 ring-line bg-white p-3">
                <video x-ref="video" autoplay playsinline class="w-full max-w-xs rounded-lg bg-navy"></video>
                <div class="mt-2 flex gap-2">
                    <button type="button" @click="capture" class="rounded-lg bg-gold-500 px-3 py-1.5 text-sm font-bold text-navy hover:bg-gold-400">{{ __('Capture') }}</button>
                    <button type="button" @click="closeWebcam" class="rounded-lg ring-1 ring-line px-3 py-1.5 text-sm font-medium text-gray-700 bg-white hover:bg-paper">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-xl border-2 border-dashed border-line bg-paper/40 p-4">
                <x-input-label for="id_document" :value="__('Identification Document')" />
                @if ($student?->id_document_path)
                    <p class="mt-1 mb-2 text-sm"><a href="{{ Storage::disk('public')->url($student->id_document_path) }}" target="_blank" class="text-gold-600 hover:underline">{{ __('View current document') }}</a></p>
                @endif
                <input id="id_document" name="id_document" type="file" accept="image/*,.pdf" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gold-800 hover:file:bg-gold-100">
                <x-input-error class="mt-2" :messages="$errors->get('id_document')" />
            </div>

            <div class="rounded-xl border-2 border-dashed border-line bg-paper/40 p-4">
                <x-input-label for="license_document" :value="__('Licence Document')" />
                @if ($student?->license_document_path)
                    <p class="mt-1 mb-2 text-sm"><a href="{{ Storage::disk('public')->url($student->license_document_path) }}" target="_blank" class="text-gold-600 hover:underline">{{ __('View current document') }}</a></p>
                @endif
                <input id="license_document" name="license_document" type="file" accept="image/*,.pdf" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gold-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-gold-800 hover:file:bg-gold-100">
                <x-input-error class="mt-2" :messages="$errors->get('license_document')" />
            </div>
        </div>
    </div>
</div>

@if (! $student)
    @include('students.partials.enrollment-fields', ['courses' => $courses, 'additionalOffers' => $additionalOffers ?? collect()])
@endif

<div>
    {!! $sectionHeading($calendarIconPath, 'Enrollment') !!}

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($fieldsLocked)
            <div>
                <x-input-label :value="__('Enrollment Date')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ optional($student->enrollment_date)->format('l, M j, Y') ?? '—' }}</p>
            </div>
            <div>
                <x-input-label :value="__('Status')" />
                <p class="mt-1 text-sm font-bold text-gray-900">{{ $statusLabels[$student->status] ?? $student->status }}</p>
            </div>
        @else
            <div>
                <x-input-label for="enrollment_date" :value="__('Enrollment Date')" />
                @if ($student)
                    <x-text-input id="enrollment_date" name="enrollment_date" type="date" class="mt-1 block w-full" :value="old('enrollment_date', optional($student->enrollment_date)->format('Y-m-d'))" :max="now()->format('Y-m-d')" required />
                @else
                    <x-text-input id="enrollment_date" type="text" class="mt-1 block w-full bg-paper" :value="now()->format('l, M j, Y')" disabled />
                    <input type="hidden" name="enrollment_date" value="{{ now()->toDateString() }}">
                    <p class="mt-1 text-xs text-gray-500">{{ __('Registration always enrolls as of today.') }}</p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('enrollment_date')" />
            </div>

            @if ($student)
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <x-select id="status" name="status" class="mt-1 block w-full" required>
                        @foreach (['active' => 'Active', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $student->status) === $value)>{{ __($label) }}</option>
                        @endforeach
                    </x-select>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Active/Completed are normally set automatically as training progresses. Use Withdrawn to record that a student has left the program.') }}</p>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>
            @endif
        @endif
    </div>
</div>
