<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
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
                        <dd class="text-sm text-gray-900 col-span-2">{{ $course->duration_hours }} {{ __('hours') }} ({{ $course->duration_weeks }} {{ __('weeks') }})</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Payment Grace Period') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $course->gracePeriodDays() }} {{ __('days') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Fee') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ number_format($course->fee, 2) }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <x-badge :color="$course->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $course->status }}</x-badge>
                        </dd>
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

                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('Students') }}</h3>

                    @if (session('status') === 'enrollment-completed')
                        <p class="mb-2 text-sm font-medium text-green-600">{{ __('Course marked as completed.') }}</p>
                    @endif
                    <x-input-error class="mb-2" :messages="$errors->get('enrollment')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <th class="px-2 py-1">{{ __('Name') }}</th>
                                    <th class="px-2 py-1">{{ __('Balance') }}</th>
                                    <th class="px-2 py-1">{{ __('Due Date') }}</th>
                                    <th class="px-2 py-1">{{ __('Status') }}</th>
                                    <th class="px-2 py-1"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($course->students as $enrolledStudent)
                                    <tr>
                                        <td class="px-2 py-1 text-sm">{{ $enrolledStudent->name }}</td>
                                        <td class="px-2 py-1 text-sm">{{ number_format($enrolledStudent->pivot->balance(), 2) }}</td>
                                        <td class="px-2 py-1 text-sm">{{ optional($enrolledStudent->pivot->due_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td class="px-2 py-1 text-sm">
                                            <x-badge :color="match ($enrolledStudent->pivot->status) {
                                                'locked' => 'red',
                                                'completed' => 'blue',
                                                default => 'green',
                                            }" class="capitalize">{{ $enrolledStudent->pivot->status }}</x-badge>
                                        </td>
                                        <td class="px-2 py-1 text-sm">
                                            @if ($enrolledStudent->pivot->status !== 'completed' && $enrolledStudent->pivot->balance() <= 0)
                                                <form method="post" action="{{ route('enrollments.complete', $enrolledStudent->pivot->id) }}" class="inline">
                                                    @csrf
                                                    @method('patch')
                                                    <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Mark Complete') }}</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-2 py-2 text-sm text-gray-500">{{ __('No students enrolled yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('courses.edit', $course) }}">
                            <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                        </a>
                    @endif
                    <a href="{{ route('courses.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
