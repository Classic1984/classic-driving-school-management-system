<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Scan Code') }}
        </h2>
    </x-slot>

    <style>
        .receipt-header {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    </style>

    <div class="py-12 print:py-0">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            @if (session('status') === 'scan-code-regenerated')
                <p class="mb-4 text-sm font-medium text-green-600 print:hidden">{{ __('Scan code regenerated. Print and hand the student this new card - the old one no longer works.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden print:shadow-none print:ring-0">
                <div class="receipt-header bg-navy-800 text-white px-8 py-6 flex items-center gap-3">
                    <x-application-logo class="h-14 w-auto" />
                    <div>
                        <p class="font-bold text-lg leading-tight">{{ __('Classic Driving School & Son Nigeria Limited') }}</p>
                        <p class="text-xs text-gold-400 uppercase tracking-widest mt-0.5">{{ __('Training Scan Code') }}</p>
                    </div>
                </div>

                <div class="px-8 py-8 text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $student->name }}</p>
                    <p class="text-sm font-mono text-gray-500">{{ $student->student_id_number }}</p>

                    <div class="mt-6 flex justify-center">
                        <x-qr-code :data="$student->trainingCheckInUrl()" class="bg-white p-3 rounded-lg ring-1 ring-gray-200 [&_svg]:h-48 [&_svg]:w-48" />
                    </div>

                    <p class="mt-6 text-sm text-gray-600">
                        {{ __('Scan this code at the front desk every training day to log your attendance automatically.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ __('Keep this card - it is unique to you and works for every training day until your course ends.') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-4 print:hidden">
                <x-secondary-button type="button" onclick="window.print()">{{ __('Print') }}</x-secondary-button>
                <a href="{{ route('students.scan-code.pdf', $student) }}" class="text-sm text-gray-600 hover:underline">{{ __('Download PDF') }}</a>
                <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to Student') }}</a>
                @if (auth()->user()->isDirector())
                    <form method="post" action="{{ route('students.scan-code.regenerate-token', $student) }}" class="ml-auto" onsubmit="return confirm('{{ __('Regenerate this student\'s scan code? Any previously printed card will stop working.') }}');">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Regenerate Code') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
