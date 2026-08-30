<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate') }}
        </h2>
    </x-slot>

    @php
        $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Manual & Automatic'];
        $transmissionLabel = $transmissionLabels[$certificate->course->course_type] ?? null;
        $levelLabel = $certificate->course->level ? ucfirst($certificate->course->level) : null;
    @endphp

    <style>
        .certificate-card {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            <div class="certificate-card relative bg-gray-900 text-white p-8 sm:p-12 rounded-lg border-4 border-amber-500 print:rounded-none text-center overflow-hidden">

                <!-- Corner accents -->
                <div class="absolute top-0 left-0 h-10 w-10 border-t-4 border-l-4 border-amber-400"></div>
                <div class="absolute top-0 right-0 h-10 w-10 border-t-4 border-r-4 border-amber-400"></div>
                <div class="absolute bottom-0 left-0 h-10 w-10 border-b-4 border-l-4 border-amber-400"></div>
                <div class="absolute bottom-0 right-0 h-10 w-10 border-b-4 border-r-4 border-amber-400"></div>

                <!-- Top-left ribbon badge -->
                <div class="absolute top-4 left-4 sm:top-6 sm:left-6 flex flex-col items-center justify-center h-20 w-20 rounded-full border-2 border-amber-400 text-amber-400 bg-gray-900">
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('SAFE') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('DRIVING') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('FOR LIFE') }}</span>
                </div>

                <!-- Bottom-right badge -->
                <div class="absolute bottom-4 right-4 sm:bottom-6 sm:right-6 hidden sm:flex flex-col items-center justify-center h-20 w-20 rounded-full border-2 border-amber-400 text-amber-400 bg-gray-900">
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('DRIVE') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('SAFE') }}</span>
                    <span class="text-[9px] font-bold leading-tight tracking-wide">{{ __('ARRIVE SAFE') }}</span>
                </div>

                <div class="text-right sm:absolute sm:top-6 sm:right-6 mb-6 sm:mb-0">
                    <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Certificate No.') }}</p>
                    <p class="text-sm font-mono text-amber-300">{{ $certificate->certificate_number }}</p>
                </div>

                <x-application-logo class="h-20 w-20 mx-auto" />
                <p class="mt-1 text-xs uppercase tracking-widest text-amber-400">{{ __('& Son Nigeria Limited') }}</p>

                <h1 class="mt-6 text-4xl font-bold tracking-wide text-amber-400">{{ __('CERTIFICATE') }}</h1>
                <p class="text-lg tracking-widest text-gray-300">{{ __('OF TRAINING COMPLETION') }}</p>

                <p class="mt-8 inline-block text-xs font-bold uppercase tracking-widest text-gray-900 bg-amber-400 rounded-full px-6 py-1.5">
                    {{ __('This is to certify that') }}
                </p>
                <p class="mt-4 font-bold text-4xl sm:text-5xl text-amber-400" style="font-family: 'Dancing Script', cursive;">
                    {{ $certificate->student->name }}
                </p>
                <div class="mt-4 border-t border-amber-500 w-2/3 mx-auto"></div>

                <p class="mt-8 text-sm text-gray-300">
                    {{ __('has successfully completed the approved training program and satisfied all the requirements in') }}
                </p>
                <p class="mt-1 text-xl font-bold text-amber-400 uppercase">{{ $certificate->course->name }}</p>
                <p class="text-sm text-gray-300">
                    {{ __('and is hereby awarded this certificate.') }}
                </p>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-5 gap-y-4 sm:gap-y-0 divide-y sm:divide-y-0 sm:divide-x divide-amber-500/50">
                    <div class="pt-4 sm:pt-0 sm:px-4 flex flex-col items-center">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Student ID') }}</p>
                        <p class="text-sm font-mono">{{ $certificate->student->student_id_number }}</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:px-4 flex flex-col items-center">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Date of Completion') }}</p>
                        <p class="text-sm">{{ $certificate->issue_date->format('jS F, Y') }}</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:px-4 flex flex-col items-center">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Duration') }}</p>
                        <p class="text-sm">{{ $certificate->course->duration_weeks }} {{ __('WEEKS') }} ({{ $certificate->course->totalTrainingDays() }} {{ __('HOURS') }})</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:px-4 flex flex-col items-center">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m3-6h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5" /></svg>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Transmission') }}</p>
                        <p class="text-sm">{{ $transmissionLabel ?? '—' }}</p>
                    </div>
                    <div class="pt-4 sm:pt-0 sm:px-4 flex flex-col items-center">
                        <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 10.5m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Level') }}</p>
                        <p class="text-sm">{{ $levelLabel ?? '—' }}</p>
                    </div>
                </div>

                <p class="mt-8 text-sm text-gray-400 max-w-xl mx-auto">
                    {{ __('We commend your dedication to becoming a responsible, skilled and safety-conscious driver.') }}
                </p>

                <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 items-end gap-6 text-left">
                    <div>
                        <p class="border-t border-gray-400 pt-1 text-sm">{{ $certificate->instructor?->name }}&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Chief Instructor') }}</p>
                    </div>
                    <div class="flex justify-center order-first sm:order-none">
                        <x-qr-code :data="$certificate->verificationUrl()" class="inline-block bg-white p-1 rounded [&_svg]:h-16 [&_svg]:w-16" />
                    </div>
                    <div class="sm:text-right">
                        <p class="border-t border-gray-400 pt-1 text-sm">&nbsp;</p>
                        <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Managing Director') }}</p>
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
