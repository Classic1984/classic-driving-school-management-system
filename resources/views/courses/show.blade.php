<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $course->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $course->description ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course Type') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $course->course_type }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $course->duration_hours }} {{ __('hours') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Fee') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ number_format($course->fee, 2) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $course->status }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Instructors') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            @forelse ($course->instructors as $courseInstructor)
                                <div>{{ $courseInstructor->name }}</div>
                            @empty
                                —
                            @endforelse
                        </dd>
                    </div>
                </dl>

                <div class="flex items-center gap-4">
                    <a href="{{ route('courses.edit', $course) }}">
                        <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('courses.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
