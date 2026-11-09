<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Issue Certificate') }}
        </h2>
    </x-slot>

    @php
        $certificateIconPath = ['M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15', 'M9 12.75 11.25 15 15 9.75'];
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-navy-900 via-navy-800 to-navy px-4 py-6 sm:px-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                        @foreach ($certificateIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>

                    <div class="relative flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                @foreach ($certificateIconPath as $path)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                @endforeach
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white">{{ __('Issue Certificate') }}</h3>
                            <p class="text-sm text-gray-400">{{ __('Issue a certificate for a completed course') }}</p>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('certificates.store') }}" class="p-4 sm:p-8 space-y-6">
                    @csrf

                    @include('certificates.partials.form-fields')

                    <div class="flex items-center gap-4 pt-4 border-t border-line">
                        <x-primary-button>{{ __('Issue Certificate') }}</x-primary-button>
                        <a href="{{ route('certificates.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
