<x-guest-layout>
    <div class="text-center">
        <x-application-logo class="h-16 w-16 mx-auto" />
        <p class="mt-1 text-xs uppercase tracking-widest text-gold-600">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
    </div>

    @if ($result === 'logged')
        <div class="mt-6 rounded-lg border-2 border-green-500 bg-green-50 p-4 text-center">
            <p class="text-lg font-bold text-green-700">✅ {{ __('Training Logged') }}</p>
            <p class="text-sm text-green-600">{{ __('Welcome, :name! Today\'s training session has been logged.', ['name' => $student->name]) }}</p>
        </div>
        <dl class="mt-6 space-y-3 text-sm">
            <div>
                <dt class="text-gray-500">{{ __('Student ID') }}</dt>
                <dd class="font-mono text-gray-900">{{ $student->student_id_number }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Course') }}</dt>
                <dd class="text-gray-900">{{ $course->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Date') }}</dt>
                <dd class="text-gray-900">{{ now()->format('l, M j, Y') }}</dd>
            </div>
            @if ($instructor)
                <div>
                    <dt class="text-gray-500">{{ __('Instructor') }}</dt>
                    <dd class="text-gray-900">{{ $instructor->name }}</dd>
                </div>
            @endif
            @if ($vehicle)
                <div>
                    <dt class="text-gray-500">{{ __('Vehicle') }}</dt>
                    <dd class="text-gray-900">{{ $vehicle->name }}</dd>
                </div>
            @endif
        </dl>
    @elseif ($result === 'choose-session-details')
        <div class="mt-6 rounded-lg border-2 border-gold-500 bg-gold-50 p-4 text-center">
            <p class="text-lg font-bold text-gold-700">{{ __('One more thing') }}</p>
            <p class="text-sm text-gold-600">{{ __('Who trained :name today, and in which vehicle? Leave either blank if not applicable.', ['name' => $student->name]) }}</p>
        </div>
        <form method="get" action="{{ route('public-training.check-in', $token) }}" class="mt-4 space-y-4">
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <input type="hidden" name="confirmed" value="1">

            <div>
                <label for="instructor_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">{{ __('Instructor') }}</label>
                <select id="instructor_id" name="instructor_id" class="block w-full rounded-md border-0 bg-white ring-1 ring-gray-300 focus:ring-gold-500 text-sm">
                    <option value="">{{ __('— Not specified —') }}</option>
                    @foreach ($instructors as $instructor)
                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="vehicle_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">{{ __('Vehicle') }}</label>
                <select id="vehicle_id" name="vehicle_id" class="block w-full rounded-md border-0 bg-white ring-1 ring-gray-300 focus:ring-gold-500 text-sm">
                    <option value="">{{ __('— Not specified —') }}</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="w-full rounded-lg bg-navy hover:bg-navy-700 px-5 py-3 text-sm font-bold text-gold-400 transition">
                {{ __('Log Training') }}
            </button>
        </form>
    @elseif ($result === 'already-logged')
        <div class="mt-6 rounded-lg border-2 border-blue-500 bg-blue-50 p-4 text-center">
            <p class="text-lg font-bold text-blue-700">{{ __('Already Logged') }}</p>
            <p class="text-sm text-blue-600">{{ __(':name has already logged training today (:course). No need to scan again.', ['name' => $student->name, 'course' => $course->name]) }}</p>
        </div>
    @elseif ($result === 'choose-course')
        <div class="mt-6 rounded-lg border-2 border-gold-500 bg-gold-50 p-4 text-center">
            <p class="text-lg font-bold text-gold-700">{{ __('Which course?') }}</p>
            <p class="text-sm text-gold-600">{{ __(':name is enrolled in more than one active course - choose which one today\'s training is for.', ['name' => $student->name]) }}</p>
        </div>
        <div class="mt-4 space-y-2">
            @foreach ($courses as $course)
                <a href="{{ route('public-training.check-in', ['token' => $token, 'course_id' => $course->id]) }}" class="block rounded-lg ring-1 ring-gray-200 hover:bg-gold-50 px-4 py-3 text-sm font-semibold text-gray-900 text-center transition">
                    {{ $course->name }}
                </a>
            @endforeach
        </div>
    @elseif ($result === 'not-eligible')
        <div class="mt-6 rounded-lg border-2 border-red-500 bg-red-50 p-4 text-center">
            <p class="text-lg font-bold text-red-700">❌ {{ __('Cannot Log Training') }}</p>
            <p class="text-sm text-red-600">
                @if ($lockedReason)
                    {{ __(':name\'s enrollment is locked (:reason). Please see the front desk.', ['name' => $student->name, 'reason' => $lockedReason]) }}
                @else
                    {{ __(':name has no active course enrollment right now. Please see the front desk.', ['name' => $student->name]) }}
                @endif
            </p>
        </div>
    @else
        <div class="mt-6 rounded-lg border-2 border-red-500 bg-red-50 p-4 text-center">
            <p class="text-lg font-bold text-red-700">❌ {{ __('Invalid Code') }}</p>
            <p class="text-sm text-red-600">{{ __('This scan code was not recognized. Please see the front desk.') }}</p>
        </div>
    @endif

    <p class="mt-8 text-center text-xs text-gray-400">
        {{ __('Classic Driving School & Son Nigeria Limited - Training Check-In') }}
    </p>
</x-guest-layout>
