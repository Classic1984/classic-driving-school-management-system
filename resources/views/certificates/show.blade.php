<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate') }}
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="p-10 bg-white shadow sm:rounded-lg border-4 border-double border-gray-400 print:border-2 print:shadow-none text-center">
                <p class="text-sm tracking-widest text-gray-500 uppercase">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                <h1 class="mt-4 text-2xl font-bold text-gray-800">{{ __('Certificate of Completion') }}</h1>

                <p class="mt-8 text-sm text-gray-600">{{ __('This is to certify that') }}</p>
                <p class="mt-2 text-xl font-semibold text-gray-900">{{ $certificate->student->name }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ __('Student ID') }}: {{ $certificate->student->id }}</p>

                <p class="mt-6 text-sm text-gray-600">{{ __('has successfully completed the') }}</p>
                <p class="mt-2 text-lg font-semibold text-gray-900">{{ $certificate->course->name }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $certificate->course->duration_hours }} {{ __('hours') }} &middot; {{ $certificate->course->duration_weeks }} {{ __('weeks') }}</p>

                <dl class="mt-10 grid grid-cols-2 gap-4 text-left max-w-sm mx-auto">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Certificate #') }}</dt>
                        <dd class="text-sm text-gray-900 font-mono">{{ $certificate->certificate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Issue Date') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $certificate->issue_date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase">{{ __('Instructor') }}</dt>
                        <dd class="text-sm text-gray-900">{{ $certificate->instructor?->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6 flex items-center gap-4 print:hidden">
                <x-secondary-button type="button" onclick="window.print()">{{ __('Print') }}</x-secondary-button>
                <a href="{{ route('certificates.edit', $certificate) }}">
                    <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                </a>
                <a href="{{ route('certificates.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
