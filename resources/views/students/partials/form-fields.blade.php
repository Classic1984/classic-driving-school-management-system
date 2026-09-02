@php($student = $student ?? null)
@php($fieldsLocked = $student && ! auth()->user()->isDirector())

<div>
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Personal Information') }}</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($fieldsLocked)
            <div class="rounded-xl bg-amber-50 ring-1 ring-amber-200 p-4 space-y-3 sm:col-span-2">
                <p class="text-xs font-medium text-amber-700">{{ __('🔒 Director-controlled information') }}</p>

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
        @endif

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

        <div>
            <x-input-label for="mother_maiden_name" :value="__('Mother Maiden Name')" />
            <x-text-input id="mother_maiden_name" name="mother_maiden_name" type="text" class="mt-1 block w-full" :value="old('mother_maiden_name', $student?->mother_maiden_name)" />
            <x-input-error class="mt-2" :messages="$errors->get('mother_maiden_name')" />
        </div>
    </div>
</div>

<div>
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Contact Information') }}</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
    </div>
</div>

<div>
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Additional Information') }}</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

        <div @if (! $student) x-show="registrationType === 'course'" @endif>
            <x-input-label for="course_type" :value="__('Course Type')" />
            <select id="course_type" name="course_type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <option value="">{{ __('Select') }}</option>
                @foreach (['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Both'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('course_type', $student?->course_type) === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('course_type')" />
        </div>

        <div @if (! $student) x-show="registrationType === 'course'" @endif>
            <x-input-label for="vehicle_class" :value="__('Class of Vehicle You Wish to Operate After Training')" />
            <select id="vehicle_class" name="vehicle_class" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <option value="">{{ __('Select') }}</option>
                @foreach (['light' => 'Light', 'heavy' => 'Heavy'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('vehicle_class', $student?->vehicle_class) === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('vehicle_class')" />
        </div>

        <div>
            <x-input-label for="license_number" :value="__('License Number')" />
            <x-text-input id="license_number" name="license_number" type="text" class="mt-1 block w-full" :value="old('license_number', $student?->license_number)" />
            <x-input-error class="mt-2" :messages="$errors->get('license_number')" />
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
    </div>
</div>

@if (! $student)
    @php($registrationType = (! old('course_id') && ! empty(old('service_ids'))) ? 'service' : 'course')
    <div>
        <x-input-label :value="__('Registration Type')" />
        <div class="mt-2 space-y-2">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="registration_type" value="course" @checked($registrationType === 'course') @change="setRegistrationType('course')" class="border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                {{ __('Enroll in a Course') }}
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="radio" name="registration_type" value="service" @checked($registrationType === 'service') @change="setRegistrationType('service')" class="border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500">
                {{ __("Register for a Service Only (Learner's Permit, Driver's Licence, Certificate) - no course") }}
            </label>
        </div>
    </div>
@endif

<fieldset class="rounded-xl ring-1 ring-gray-200 p-4">
    <legend class="text-sm font-bold uppercase tracking-wider text-gray-500 px-1">{{ __('Next of Kin') }}</legend>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Documents') }}</h3>

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
        >
            <x-input-label for="photo" :value="__('3 Colour Passport Size Photograph')" />
            @if ($student?->photo_path)
                <img src="{{ Storage::url($student->photo_path) }}" alt="{{ __('Current photo') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-md border border-gray-200">
            @endif
            <template x-if="previewUrl">
                <img :src="previewUrl" alt="{{ __('Captured photo preview') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-md border border-amber-400">
            </template>

            <div class="mt-1 flex items-center gap-3">
                <input id="photo" name="photo" x-ref="photoInput" type="file" accept="image/*" class="block w-full text-sm border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <button type="button" @click="openWebcam" class="shrink-0 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('Use Webcam') }}
                </button>
            </div>

            <p x-show="error" x-text="error" class="mt-1 text-sm text-red-600"></p>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />

            <div x-show="webcamOpen" class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3">
                <video x-ref="video" autoplay playsinline class="w-full max-w-xs rounded-md bg-black"></video>
                <div class="mt-2 flex gap-2">
                    <button type="button" @click="capture" class="rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600">{{ __('Capture') }}</button>
                    <button type="button" @click="closeWebcam" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="id_document" :value="__('Identification Document')" />
                @if ($student?->id_document_path)
                    <p class="mt-1 mb-2 text-sm"><a href="{{ Storage::url($student->id_document_path) }}" target="_blank" class="text-amber-600 hover:underline">{{ __('View current document') }}</a></p>
                @endif
                <input id="id_document" name="id_document" type="file" accept="image/*,.pdf" class="mt-1 block w-full text-sm border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <x-input-error class="mt-2" :messages="$errors->get('id_document')" />
            </div>

            <div>
                <x-input-label for="license_document" :value="__('Licence Document')" />
                @if ($student?->license_document_path)
                    <p class="mt-1 mb-2 text-sm"><a href="{{ Storage::url($student->license_document_path) }}" target="_blank" class="text-amber-600 hover:underline">{{ __('View current document') }}</a></p>
                @endif
                <input id="license_document" name="license_document" type="file" accept="image/*,.pdf" class="mt-1 block w-full text-sm border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                <x-input-error class="mt-2" :messages="$errors->get('license_document')" />
            </div>
        </div>
    </div>
</div>

@if (! $student)
    @include('students.partials.enrollment-fields', ['courses' => $courses, 'additionalOffers' => $additionalOffers ?? collect()])
@endif

<div>
    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">{{ __('Enrollment') }}</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
    </div>
</div>
