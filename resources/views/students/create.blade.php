<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Register Student') }}
        </h2>
    </x-slot>

    @php
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
    @endphp

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6 px-4 sm:px-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Register Student') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Add a new student to the school') }}</p>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                <form method="post" action="{{ route('students.store') }}" class="space-y-6" enctype="multipart/form-data" x-data="{
                    registrationType: '{{ (! old('course_id') && ! empty(old('service_ids'))) ? 'service' : 'course' }}',
                    setRegistrationType(type) {
                        this.registrationType = type;
                        // Switching to Service Only doesn't erase a course
                        // already picked before the switch - clear it so
                        // the server sees a clean service-only submission
                        // rather than treating it as a course enrollment.
                        if (type === 'service') {
                            const courseSelect = document.getElementById('course_id');
                            if (courseSelect) {
                                courseSelect.value = '';
                                courseSelect.dispatchEvent(new Event('change'));
                            }
                        }
                    },
                }">
                    @csrf

                    @include('students.partials.form-fields')

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
