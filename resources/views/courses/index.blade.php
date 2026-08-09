<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Courses') }}
                    </h3>

                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('courses.create') }}">
                            <x-primary-button type="button">{{ __('Add Course') }}</x-primary-button>
                        </a>
                    @endif
                </div>

                @if (session('status') === 'course-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course created successfully.') }}</p>
                @elseif (session('status') === 'course-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course updated successfully.') }}</p>
                @elseif (session('status') === 'course-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Type') }}</th>
                                <th class="px-4 py-2">{{ __('Schedule') }}</th>
                                <th class="px-4 py-2">{{ __('Duration') }}</th>
                                <th class="px-4 py-2">{{ __('Fee') }}</th>
                                <th class="px-4 py-2">{{ __('Instructors') }}</th>
                                <th class="px-4 py-2">{{ __('Students') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($courses as $course)
                                <tr>
                                    <td class="px-4 py-2">{{ $course->name }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $course->course_type }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="$course->isWeekend() ? 'blue' : 'gray'" class="capitalize">{{ $course->schedule }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2">{{ $course->duration_hours }}h</td>
                                    <td class="px-4 py-2">{{ number_format($course->fee, 2) }}</td>
                                    <td class="px-4 py-2">{{ $course->instructors->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $course->students->count() }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="$course->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $course->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('courses.show', $course) }}" class="text-sm text-amber-600 hover:underline">{{ __('View') }}</a>
                                        @if (auth()->user()->canManageCourses())
                                            <a href="{{ route('courses.edit', $course) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        @endif
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('courses.destroy', $course) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this course?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No courses created yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $courses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
