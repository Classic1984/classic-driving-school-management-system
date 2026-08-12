<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enroll :name in a Course', ['name' => $student->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl">
                @if ($courses->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('There are no active courses left to enroll this student in - either add a new course, or they are already enrolled in every active one.') }}</p>
                    <a href="{{ route('students.show', $student) }}" class="mt-4 inline-block text-sm text-gray-600 hover:underline">{{ __('Back to :name', ['name' => $student->name]) }}</a>
                @else
                    <form method="post" action="{{ route('students.enroll.store', $student) }}" class="space-y-6">
                        @csrf

                        @include('students.partials.enrollment-fields', ['courses' => $courses, 'legend' => __('Course Enrollment & Initial Payment')])

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Enroll') }}</x-primary-button>
                            <a href="{{ route('students.show', $student) }}" class="text-sm text-gray-600 hover:underline">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
