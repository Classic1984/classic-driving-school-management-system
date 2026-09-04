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
            <div class="certificate-card relative bg-gray-900 text-white p-1.5 sm:p-2 rounded-2xl print:rounded-none overflow-hidden">
                <div class="relative border-2 border-amber-500/70 rounded-xl p-8 sm:p-14 text-center">
                    <div class="pointer-events-none absolute inset-3 rounded-lg border border-amber-500/25"></div>

                    <!-- Corner flourishes -->
                    <svg class="absolute top-3 left-3 h-8 w-8 text-amber-500/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" d="M2 13V4a2 2 0 0 1 2-2h9" /><path stroke-linecap="round" d="M2 2l9 9" /></svg>
                    <svg class="absolute top-3 right-3 h-8 w-8 text-amber-500/60 -scale-x-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" d="M2 13V4a2 2 0 0 1 2-2h9" /><path stroke-linecap="round" d="M2 2l9 9" /></svg>
                    <svg class="absolute bottom-3 left-3 h-8 w-8 text-amber-500/60 -scale-y-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" d="M2 13V4a2 2 0 0 1 2-2h9" /><path stroke-linecap="round" d="M2 2l9 9" /></svg>
                    <svg class="absolute bottom-3 right-3 h-8 w-8 text-amber-500/60 -scale-x-100 -scale-y-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" d="M2 13V4a2 2 0 0 1 2-2h9" /><path stroke-linecap="round" d="M2 2l9 9" /></svg>

                    <!-- Top row: FRSC seal · Certificate No -->
                    <div class="relative flex items-start justify-between gap-3 text-left">
                        <div class="flex items-center gap-2">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-amber-400 bg-white p-1 overflow-hidden">
                                <img src="{{ asset('images/frsc-logo.jpg') }}" alt="{{ __('FRSC') }}" class="h-full w-full object-contain">
                            </span>
                            <div>
                                <p class="text-[8px] uppercase tracking-wide text-amber-400 leading-tight">{{ __('FRSC Approved No.') }}</p>
                                <p class="text-[9px] font-mono text-amber-300 leading-tight">FRSC/008679/RV/0042</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[9px] uppercase tracking-widest text-amber-400">{{ __('Certificate No.') }}</p>
                            <p class="text-sm font-mono text-amber-300">{{ $certificate->certificate_number }}</p>
                        </div>
                    </div>

                    <x-application-logo class="h-16 w-16 mx-auto mt-4" />
                    <p class="mt-1 text-[11px] uppercase tracking-[0.3em] text-amber-400">{{ __('& Son Nigeria Limited') }}</p>

                    <div class="mt-5 flex items-center justify-center gap-3">
                        <span class="h-px w-10 bg-amber-500/60"></span>
                        <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.5 6.5L21 11l-6.5 2.5L12 20l-2.5-6.5L3 11l6.5-2.5L12 2z" /></svg>
                        <span class="h-px w-10 bg-amber-500/60"></span>
                    </div>
                    <h1 class="mt-3 text-3xl sm:text-4xl font-bold tracking-wide text-amber-400" style="font-family: Georgia, 'Times New Roman', serif;">{{ __('Certificate of Training Completion') }}</h1>

                    <p class="mt-8 text-sm text-gray-300">{{ __('This is to certify that') }}</p>
                    <p class="mt-3 font-bold text-4xl sm:text-5xl text-amber-400" style="font-family: 'Dancing Script', cursive;">
                        {{ $certificate->student->name }}
                    </p>
                    <div class="mt-3 flex items-center justify-center gap-3">
                        <span class="h-px w-16 bg-amber-500/60"></span>
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        <span class="h-px w-16 bg-amber-500/60"></span>
                    </div>

                    <p class="mt-6 text-sm text-gray-300 max-w-xl mx-auto">
                        {{ __('has successfully completed the approved training program and satisfied all the requirements in') }}
                    </p>
                    <p class="mt-1 text-xl font-bold text-amber-400 uppercase tracking-wide">{{ $certificate->course->name }}</p>
                    <p class="text-sm text-gray-300">
                        {{ __('and is hereby awarded this certificate.') }}
                    </p>

                    <div class="mt-10 grid grid-cols-2 sm:grid-cols-5 gap-y-5 gap-x-2 rounded-xl bg-black/30 ring-1 ring-amber-500/20 px-4 py-5">
                        <div class="flex flex-col items-center">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0" /></svg>
                            <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Student ID') }}</p>
                            <p class="text-sm font-mono">{{ $certificate->student->student_id_number }}</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                            <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Date of Completion') }}</p>
                            <p class="text-sm">{{ $certificate->issue_date->format('jS F, Y') }}</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Duration') }}</p>
                            <p class="text-sm">{{ $certificate->course->duration_weeks }} {{ __('WEEKS') }} ({{ $certificate->course->totalTrainingDays() }} {{ __('HOURS') }})</p>
                        </div>
                        <div class="flex flex-col items-center">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m3-6h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5" /></svg>
                            <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Transmission') }}</p>
                            <p class="text-sm">{{ $transmissionLabel ?? '—' }}</p>
                        </div>
                        <div class="flex flex-col items-center col-span-2 sm:col-span-1">
                            <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 10.5m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                            <p class="mt-1 text-[10px] uppercase tracking-widest text-amber-400">{{ __('Level') }}</p>
                            <p class="text-sm">{{ $levelLabel ?? '—' }}</p>
                        </div>
                    </div>

                    <p class="mt-8 text-sm text-gray-400 max-w-xl mx-auto italic">
                        {{ __('We commend your dedication to becoming a responsible, skilled and safety-conscious driver.') }}
                    </p>

                    <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 items-end gap-8">
                        <div class="text-center sm:text-left">
                            <p class="border-t border-amber-500/40 pt-2 text-sm">{{ $certificate->instructor?->name }}&nbsp;</p>
                            <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Chief Instructor') }}</p>
                        </div>
                        <div class="flex justify-center order-first sm:order-none">
                            <x-qr-code :data="$certificate->verificationUrl()" class="bg-white p-1.5 rounded-md [&_svg]:h-16 [&_svg]:w-16" />
                        </div>
                        <div class="text-center sm:text-right">
                            <p class="border-t border-amber-500/40 pt-2 text-sm">&nbsp;</p>
                            <p class="text-[10px] uppercase tracking-widest text-amber-400">{{ __('Managing Director') }}</p>
                        </div>
                    </div>

                    <p class="mt-8 italic text-amber-400 text-sm">{{ __('"When you say Classic, you say it all."') }}</p>

                    <div class="mt-6 pt-4 border-t border-amber-500/30 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[10px] text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                            {{ __('2 Trans Woji Elelenwo Road, YKC Junction, Woji, Port Harcourt') }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                            {{ __('0806 887 8663 · 0809 476 0609') }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-1.657 0-3-4.03-3-9s1.343-9 3-9 3 4.03 3 9-1.343 9-3 9ZM3.75 9h16.5M3.75 15h16.5" /></svg>
                            {{ __('classicdriving.com.ng') }}
                        </span>
                    </div>
                </div>
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
