<x-guest-layout>
    <div class="text-center">
        <x-application-logo class="h-16 w-16 mx-auto" />
        <p class="mt-1 text-xs uppercase tracking-widest text-amber-600">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
    </div>

    @if ($certificate)
        <div class="mt-6 rounded-lg border-2 border-green-500 bg-green-50 p-4 text-center">
            <p class="text-lg font-bold text-green-700">✅ {{ __('Certificate Verified') }}</p>
            <p class="text-sm text-green-600">{{ __('This certificate is genuine and on file.') }}</p>
        </div>

        <dl class="mt-6 space-y-3 text-sm">
            <div>
                <dt class="text-gray-500">{{ __('Certificate No.') }}</dt>
                <dd class="font-mono font-semibold text-gray-900">{{ $certificate->certificate_number }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Name') }}</dt>
                <dd class="font-semibold text-gray-900">{{ $certificate->student->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Student ID') }}</dt>
                <dd class="font-mono text-gray-900">{{ $certificate->student->student_id_number }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Programme') }}</dt>
                <dd class="text-gray-900">{{ $certificate->course->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Date Issued') }}</dt>
                <dd class="text-gray-900">{{ $certificate->issue_date->format('jS F, Y') }}</dd>
            </div>
            @if ($certificate->instructor)
                <div>
                    <dt class="text-gray-500">{{ __('Instructor') }}</dt>
                    <dd class="text-gray-900">{{ $certificate->instructor->name }}</dd>
                </div>
            @endif
        </dl>
    @else
        <div class="mt-6 rounded-lg border-2 border-red-500 bg-red-50 p-4 text-center">
            <p class="text-lg font-bold text-red-700">❌ {{ __('Not Verified') }}</p>
            <p class="text-sm text-red-600">{{ __('No certificate matches this number. This certificate could not be confirmed as genuine.') }}</p>
        </div>
    @endif

    <p class="mt-8 text-center text-xs text-gray-400">
        {{ __('Verified against the Classic Driving School & Son Nigeria Limited certificate register.') }}
    </p>
</x-guest-layout>
