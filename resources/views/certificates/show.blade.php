<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate') }}
        </h2>
    </x-slot>

    <style>
        .certificate-card {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="certificate-card relative bg-gray-900 text-white p-10 sm:p-14 rounded-lg border-4 border-amber-500 print:rounded-none text-center">

                <div class="absolute top-6 left-6 flex flex-col items-center justify-center h-20 w-20 rounded-full border-2 border-amber-400 text-amber-400">
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('SAFETY') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('SKILL') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('CONFIDENCE') }}</span>
                </div>

                <div class="absolute top-6 right-6 text-right">
                    <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Certificate No.') }}</p>
                    <p class="text-sm font-mono">{{ $certificate->certificate_number }}</p>
                    <x-qr-code :data="$certificate->verificationUrl()" class="mt-2 inline-block bg-white p-1 rounded [&_svg]:h-16 [&_svg]:w-16" />
                </div>

                <x-application-logo class="h-20 w-20 mx-auto" />
                <p class="mt-1 text-xs uppercase tracking-widest text-amber-400">{{ __('& Son Nigeria Limited') }}</p>

                <h1 class="mt-6 text-4xl font-bold tracking-wide text-amber-400">{{ __('CERTIFICATE') }}</h1>
                <p class="text-lg tracking-widest text-gray-300">{{ __('OF TRAINING') }}</p>

                <p class="mt-8 text-sm uppercase tracking-widest text-gray-300">{{ __('This is to certify that') }}</p>
                <p class="mt-2 font-bold text-4xl sm:text-5xl text-amber-400" style="font-family: 'Dancing Script', cursive;">
                    {{ $certificate->student->name }}
                </p>
                <p class="text-xs text-gray-400 font-mono">{{ $certificate->student->student_id_number }}</p>
                <div class="mt-4 border-t border-amber-500 w-2/3 mx-auto"></div>

                <p class="mt-8 text-sm text-gray-300">
                    {{ __('has successfully completed the training program in') }}
                </p>
                <p class="mt-1 text-xl font-bold text-amber-400 uppercase">{{ $certificate->course->name }}</p>
                <p class="text-sm text-gray-300">
                    {{ __('conducted by Classic Driving School & Son Nigeria Limited.') }}
                </p>
                <p class="mt-4 text-sm text-gray-400 max-w-xl mx-auto">
                    {{ __('The bearer has demonstrated the knowledge, skills and discipline required to be a responsible and safe driver.') }}
                </p>

                <div class="mt-10 grid grid-cols-2 sm:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-amber-500/50 text-left">
                    <div class="pb-4 sm:pb-0 sm:pr-4">
                        <p class="invisible pt-1 text-sm">&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Date of Completion') }}</p>
                        <p class="text-sm">{{ $certificate->issue_date->format('jS F, Y') }}</p>
                    </div>
                    <div class="pb-4 sm:pb-0 sm:px-4">
                        <p class="invisible pt-1 text-sm">&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Duration') }}</p>
                        <p class="text-sm">{{ $certificate->course->duration_weeks }} {{ __('WEEKS') }} ({{ $certificate->course->totalTrainingDays() }} {{ __('HOURS') }})</p>
                    </div>
                    <div class="pb-4 sm:pb-0 sm:px-4">
                        <p class="invisible pt-1 text-sm">&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Program') }}</p>
                        <p class="text-sm">{{ $certificate->course->name }}</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:px-4">
                        <p class="border-t border-gray-400 pt-1 text-sm">&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Director') }}</p>
                        <p class="text-[10px] text-gray-400">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:pl-4">
                        <p class="border-t border-gray-400 pt-1 text-sm">{{ $certificate->instructor?->name }}&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Instructor') }}</p>
                        <p class="text-[10px] text-gray-400">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                    </div>
                </div>

                <p class="mt-8 italic text-amber-400">{{ __('"When you say Classic, you say it all."') }}</p>
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
