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
        </dl>
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
