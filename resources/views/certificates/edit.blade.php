<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Certificate') }}
        </h2>
    </x-slot>

    @php
        $certificateIconPath = ['M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15', 'M9 12.75 11.25 15 15 9.75'];
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6 px-4 sm:px-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($certificateIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ __('Edit Certificate') }}</h3>
                    <p class="text-sm font-mono text-gray-500 truncate">{{ $certificate->certificate_number }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <form method="post" action="{{ route('certificates.update', $certificate) }}" class="space-y-6">
                    @csrf
                    @method('put')

                    @include('certificates.partials.form-fields')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('certificates.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
